<?php

namespace App\Service\Vehicle;

use App\Entity\Depot;
use App\Entity\DriverHistory;
use App\Entity\FuelType\FuelType;
use App\Entity\Note;
use App\Entity\Notification\Event;
use App\Entity\Setting;
use App\Entity\Team;
use App\Entity\User;
use App\Entity\Vehicle;
use App\Entity\VehicleGroup;
use App\Entity\VehicleType;
use App\Enums\EntityHistoryTypes;
use App\Events\Depot\VehicleAddedToDepotEvent;
use App\Events\Depot\VehicleRemovedFromDepotEvent;
use App\Events\Reminder\ReminderEsRefreshEvent;
use App\Events\Vehicle\VehicleStatusChangedEvent;
use App\Events\Vehicle\VehicleUpdatedEvent;
use App\Exceptions\ValidationException;
use App\Service\Client\ClientService;
use App\Service\Validation\ValidationService;
use App\Service\VehicleGroup\VehicleGroupService;
use Carbon\Carbon;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Criteria;

trait VehicleServiceFieldsTrait
{
    /**
     * @param array $data
     * @param Vehicle $vehicle
     * @param User $currentUser
     * @return Vehicle
     */
    private function handleDepot(array $data, Vehicle $vehicle, User $currentUser): Vehicle
    {
        if (array_key_exists('depotId', $data)) {
            $oldDepot = $vehicle->getDepot();

            if ($newDepotId = $data['depotId']) {
                $depot = $this->em->getRepository(Depot::class)->find($newDepotId);

                if ($depot && ClientService::checkTeamAccess($depot->getTeam(), $currentUser)) {
                    $vehicle->setDepot($depot);
                    $this->eventDispatcher->dispatch(
                        new VehicleAddedToDepotEvent($vehicle, $depot, $currentUser),
                        VehicleAddedToDepotEvent::NAME
                    );
                }
            } else {
                $vehicle->setDepot(null);
            }

            if ($oldDepot && $vehicle->getDepot() !== $oldDepot) {
                $this->eventDispatcher->dispatch(
                    new VehicleRemovedFromDepotEvent($vehicle, $oldDepot, $currentUser),
                    VehicleRemovedFromDepotEvent::NAME
                );
            }
        }

        return $vehicle;
    }

    /**
     * @param Vehicle $vehicle
     * @param array $data
     * @param $currentUser
     * @return Vehicle
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    private function unavailableHandler(Vehicle $vehicle, array $data, $currentUser)
    {
        if (!isset($data['isUnavailable'])) {
            return $vehicle;
        }

        $isUnavailable = filter_var($data['isUnavailable'], FILTER_VALIDATE_BOOLEAN);

        if (isset($data['isUnavailable']) && true === $isUnavailable && !$vehicle->isUnavailable()) {
            $this->entityHistoryService->create(
                $vehicle,
                $vehicle->getStatus(),
                EntityHistoryTypes::VEHICLE_STATUS,
                $currentUser
            );
            if ($data['unavailableMessage'] ?? null) {
                $vehicle->setUnavailableMessage($data['unavailableMessage']);
            }
            $vehicle->makeUnavailable();
            $this->notificationDispatcher->dispatch(Event::VEHICLE_UNAVAILABLE, $vehicle);
        } elseif (isset($data['isUnavailable']) && false === $isUnavailable && $vehicle->isUnavailable()) {
            $statusHistory = $this->entityHistoryService->listWithExclude(
                Vehicle::class,
                $vehicle->getId(),
                EntityHistoryTypes::VEHICLE_STATUS,
                [Vehicle::STATUS_DELETED, Vehicle::STATUS_UNAVAILABLE]
            )->first();

            if ($statusHistory) {
                $vehicle->setStatus($statusHistory->getPayload());
            } else {
                $vehicle->setStatus(Vehicle::STATUS_OFFLINE);
            }
            $this->eventDispatcher->dispatch(new VehicleStatusChangedEvent($vehicle), VehicleStatusChangedEvent::NAME);
        }

        return $vehicle;
    }

    /**
     * @param array $fields
     * @param User $currentUser
     * @param Vehicle|null $editVehicle
     */
    private function validateVehicleFields(array $fields, User $currentUser, ?Vehicle $editVehicle = null)
    {
        $errors = [];
        if ($currentUser->isInAdminTeam()) {
            if (!isset($fields['teamId']) || !$fields['teamId']) {
                $errors['teamId'] = ['required' => $this->translator->trans('validation.errors.field.required')];
            }
            if ($fields['teamId'] ?? null) {
                $team = $this->em->getRepository(Team::class)->find($fields['teamId']);
                if (!$currentUser->isInstaller() && (!$team || !ClientService::checkTeamAccess($team, $currentUser))) {
                    $errors['teamId'] = ['required' => $this->translator->trans('validation.errors.field.wrong_value')];
                }
            }
        }

        if (isset($fields['depotId']) && !is_null($fields['depotId'])) {
            $depot = $this->em->getRepository(Depot::class)->find($fields['depotId']);
            if (!$depot || !ClientService::checkTeamAccess($depot->getTeam(), $currentUser)) {
                $errors['depotId'] = ['required' => $this->translator->trans('validation.errors.field.wrong_value')];
            }
        }

        if (isset($fields['type']) && !is_null($fields['type'])) {
            $type = $this->em->getRepository(VehicleType::class)->findOneBy(['name' => $fields['type']]);
            if (!$type || ($type->getTeam() && !ClientService::checkTeamAccess($type->getTeam(), $currentUser))) {
                $errors['type'] = ['required' => $this->translator->trans('validation.errors.field.wrong_value')];
            }
        } elseif (isset($fields['typeId']) && !is_null($fields['typeId'])) {
            $type = $this->em->getRepository(VehicleType::class)->find($fields['typeId']);
            if (!$type || ($type->getTeam() && !ClientService::checkTeamAccess($type->getTeam(), $currentUser))) {
                $errors['type'] = ['required' => $this->translator->trans('validation.errors.field.wrong_value')];
            }
        } elseif (!$editVehicle) {
            $type = $this->em->getRepository(VehicleType::class)->findOneBy(['name' => VehicleType::CAR]);
            if (!$type) {
                $errors['type'] = ['required' => $this->translator->trans('validation.errors.field.required')];
            }
        }

        if ($fields['groupId'] ?? null) {
            $group = $this->em->getRepository(VehicleGroup::class)->find($fields['groupId']);
            if (!$group || !VehicleGroupService::checkVehicleGroupAccess($group, $currentUser)) {
                $errors['groupId'] = ['required' => $this->translator->trans('validation.errors.field.wrong_value')];
            }
        }

        if ($fields['regDate'] ?? null) {
            $errors = $this->validationService->validateDate($fields, 'regDate', $errors, ValidationService::LESS_THAN);
        }

        if (!isset($errors['teamId']) && ($fields['vin'] ?? null)) {
            if ($currentUser->isInClientTeam()) {
                $team = $currentUser->getTeam();
            } else {
                $team = $this->em->getRepository(Team::class)->find($fields['teamId']);
            }
            if ($editVehicle) {
                $vehicle = $this->em->getRepository(Vehicle::class)
                    ->getVehicleIdByVinExcludeCurrent($team, $editVehicle->getId(), $fields['vin']);
            } else {
                $vehicle = $this->em->getRepository(Vehicle::class)->findBy(['vin' => $fields['vin'], 'team' => $team]);
            }

            if ($vehicle) {
                $errors['vin'] = ['required' => $this->translator->trans('entities.vehicle.vin')];
            }
        }

        if (!isset($errors['teamId']) && ($fields['regNo'] ?? null)) {
            if ($currentUser->isInClientTeam()) {
                $team = $currentUser->getTeam();
            } else {
                $team = $this->em->getRepository(Team::class)->find($fields['teamId']);
            }
            if ($editVehicle) {
                $vehicle = $this->em->getRepository(Vehicle::class)
                    ->getVehicleIdByRegNoExcludeCurrent($team, $editVehicle, $fields['regNo']);
            } else {
                $vehicle = $this->em->getRepository(Vehicle::class)
                    ->findBy(['regNo' => $fields['regNo'], 'team' => $team]);
            }

            if ($vehicle) {
                $errors['regNo'] = ['required' => $this->translator->trans('entities.vehicle.regNo')];
            }
        }

        if (count($errors)) {
            throw (new ValidationException())->setErrors($errors);
        }
    }

    private function handleCreateFields(Vehicle $vehicle, User $currentUser, array $data): Vehicle
    {
        if ($currentUser->isInClientTeam()) {
            $vehicle->setTeam($currentUser->getTeam());
        } else {
            $team = $data['teamId'] ? $this->em->getRepository(Team::class)->find($data['teamId']) : null;
            $vehicle->setTeam($team);
        }

        if (isset($data['type'])) {
            $type = $this->em->getRepository(VehicleType::class)->findOneBy(['name' => $data['type']]);
        } elseif (isset($data['typeId'])) {
            $type = $this->em->getRepository(VehicleType::class)->find($data['typeId']);
        } else {
            $type = $this->em->getRepository(VehicleType::class)->findOneBy(['name' => VehicleType::CAR]);
        }

        if (isset($type)) {
            $vehicle->setType($type);
        }

        $groups = isset($data['groupIds'])
            ? $this->em->getRepository(VehicleGroup::class)->findBy($data['groupIds'])
            : null;

        if (is_array($groups)) {
            $vehicle = $this->addVehicleToGroups($vehicle, new ArrayCollection($groups), $currentUser);
        }

        $depot = isset($data['depotId']) && !is_null($data['depotId'])
            ? $this->em->getRepository(Depot::class)->find($data['depotId'])
            : null;
        if ($depot) {
            $vehicle->setDepot($depot);
        }

        if ($data['regDate'] ?? null) {
            $vehicle->setRegDate(self::parseDateToUTC($data['regDate']));
        }

        $data['fuelType'] = isset($data['fuelType'])
            ? $this->em->getRepository(FuelType::class)->find(['id' => $data['fuelType']])
            : null;

        if ($data['fuelType'] ?? null) {
            $vehicle->setFuelType($data['fuelType']);
        }
        $vehicle = $this->unavailableHandler($vehicle, $data, $currentUser);

        if ($data['picture'] ?? null) {
            $picture = $this->fileService->uploadVehiclePictureFile($data['picture'], $currentUser);
            $vehicle->setPicture($picture);
        }

        if (!($data['ecoSpeed'] ?? null)) {
            $ecoSpeedSetting = $currentUser->getSettingByName(Setting::ECO_SPEED);
            $vehicle->setEcoSpeed($ecoSpeedSetting ? $ecoSpeedSetting->getValue()['value'] : null);
        }

        if (!($data['excessiveIdling'] ?? null)) {
            $ecoSpeedSetting = $currentUser->getSettingByName(Setting::EXCESSIVE_IDLING);
            $vehicle->setExcessiveIdling($ecoSpeedSetting ? $ecoSpeedSetting->getValue()['value'] : null);
        }

        return $vehicle;
    }

    public function handleNotesFields(Vehicle $vehicle, User $currentUser, array $data)
    {
        if ($data['clientNote'] ?? null) {
            $this->noteService->create(
                [
                    'note' => $data['clientNote'],
                    'vehicle' => $vehicle,
                    'noteType' => Note::TYPE_CLIENT,
                    'createdBy' => $currentUser
                ]
            );
        }
        if (!$currentUser->isInClientTeam() && ($data['adminNote'] ?? null)) {
            $this->noteService->create(
                [
                    'note' => $data['adminNote'],
                    'vehicle' => $vehicle,
                    'noteType' => Note::TYPE_ADMIN,
                    'createdBy' => $currentUser
                ]
            );
        }
    }

    private function handleEditFields(Vehicle $vehicle, User $currentUser, array $data)
    {
        if ($currentUser->isInAdminTeam()) {
            $team = isset($data['teamId']) ? $this->em->getRepository(Team::class)->find($data['teamId']) : null;
            if ($team && ClientService::checkTeamAccess($team, $currentUser)) {
                $vehicle->setTeam($team);
            }
        }

        $groups = isset($data['groupIds'])
            ? $this->em->getRepository(VehicleGroup::class)->findBy(['id' => $data['groupIds']])
            : null;
        if (is_array($groups)) {
            $vehicle = $this->addVehicleToGroups($vehicle, new ArrayCollection($groups), $currentUser);
        }

        if ($data['picture'] ?? null) {
            $picture = $this->fileService->uploadVehiclePictureFile($data['picture'], $currentUser);
            $vehicle->setPicture($picture);
        }

        $vehicle->setUpdatedBy($currentUser);
        $vehicle->setUpdatedAt(new \DateTime());

        return $vehicle;
    }

    private function handleEditEvents(
        Vehicle $vehicle,
        Vehicle $prevVehicle,
        array $data,
        ?DriverHistory $prevDriverHistory
    ) {
        $this->eventDispatcher->dispatch(new VehicleUpdatedEvent($vehicle), VehicleUpdatedEvent::NAME);
        $this->eventDispatcher->dispatch(new ReminderEsRefreshEvent($vehicle), ReminderEsRefreshEvent::NAME);

        if ($prevDriverHistory && $prevDriverHistory->getDriver()) {
            if (isset($data['driverId']) && $data['driverId'] != $prevDriverHistory->getDriver()->getId()) {
                $this->sendDriverChangedEvent($vehicle, $prevDriverHistory);
            }
        } elseif (isset($data['driverId']) && $data['driverId']) {  //if prev history === null and prev driver === null
            $this->sendDriverChangedEvent($vehicle, null);
        }

        if (isset($data['regNo']) && $data['regNo'] !== $prevVehicle->getRegNo()) {
            $this->notificationDispatcher->dispatch(
                Event::VEHICLE_CHANGED_REGNO, $vehicle, null, ['oldValue' => $prevVehicle->getRegNo()]
            );
        }
        if ((isset($data['make']) && $data['make'] !== $prevVehicle->getMake())
            || (isset($data['makeModel']) && $data['makeModel'] !== $prevVehicle->getMakeModel())
        ) {
            $this->notificationDispatcher->dispatch(
                Event::VEHICLE_CHANGED_MODEL, $vehicle, null, ['oldValue' => $prevVehicle->getModel()]
            );
        }
    }
}