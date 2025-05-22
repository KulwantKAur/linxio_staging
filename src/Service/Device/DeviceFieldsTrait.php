<?php


namespace App\Service\Device;


use App\Entity\Device;
use App\Entity\DeviceModel;
use App\Entity\Note;
use App\Entity\Notification\Event;
use App\Entity\StreamaxIntegration;
use App\Entity\Team;
use App\Entity\User;
use App\Entity\Vehicle;
use App\Events\Device\DeviceChangedTeamEvent;
use App\Events\Device\DeviceCreatedEvent;
use App\Events\Device\DeviceDeactivatedEvent;
use App\Events\Device\DevicePhoneChangedEvent;
use App\Events\Device\DeviceUnavailableEvent;
use App\Events\Device\DeviceContractChangedEvent;
use App\Events\Device\DeviceUninstalledEvent;
use App\Events\Vehicle\VehicleStatusChangedEvent;
use App\Exceptions\ValidationException;
use App\Service\Client\ClientService;
use App\Service\Vehicle\VehicleService;

trait DeviceFieldsTrait
{
    public function handleNotesFields(array $data, Device $device, User $user)
    {
        if ($data['clientNote'] ?? null) {
            $this->noteService->create(
                [
                    'note' => $data['clientNote'],
                    'device' => $device,
                    'noteType' => Note::TYPE_CLIENT,
                    'createdBy' => $user
                ]
            );
        }
        if (!$user->isInClientTeam() && ($data['adminNote'] ?? null)) {
            $this->noteService->create(
                [
                    'note' => $data['adminNote'],
                    'device' => $device,
                    'noteType' => Note::TYPE_ADMIN,
                    'createdBy' => $user
                ]
            );
        }
    }


    /**
     * @param array $fields
     * @param User $currentUser
     * @throws ValidationException
     */
    private function validateInstallDeviceFields(array $fields, User $currentUser)
    {
        $errors = [];

        if (!isset($fields['vehicleId']) || !$fields['vehicleId']) {
            $errors['vehicle'] = ['required' => $this->translator->trans('validation.errors.field.required')];
        }

        if ($fields['vehicleId'] ?? null) {
            $vehicle = $this->em->getRepository(Vehicle::class)->getVehicleByIdForInstall($currentUser,
                $fields['vehicleId']);
            if (!$vehicle) {
                $errors['Vehicle permissions error'] = ['required' => $this->translator->trans('entities.vehicle.vehicle_permission')];
            }
        }

        if (count($errors)) {
            throw (new ValidationException())->setErrors($errors);
        }
    }

    /**
     * @param array $fields
     * @param User|null $currentUser
     * @throws ValidationException
     */
    private function validateDeviceFields(array $fields, User $currentUser = null, Device $device = null): array
    {
        $errors = [];

        if ($currentUser->isInClientTeam()) {
            $fields = array_filter($fields, function ($key) {
                return in_array($key, Device::CLIENT_EDITABLE_FIELDS);
            }, ARRAY_FILTER_USE_KEY);
        }

        if (!$device && (!isset($fields['modelId']) || !$fields['modelId'])) {
            $errors['modelId'] = ['required' => $this->translator->trans('validation.errors.field.required')];
        }

        if ($fields['modelId'] ?? null) {
            $deviceModel = $this->em->getRepository(DeviceModel::class)->find($fields['modelId']);
            if (!$deviceModel) {
                $errors['modelId'] = ['required' => $this->translator->trans('validation.errors.field.wrong_value')];
            }
        }

        if (!$device && (!isset($fields['imei']) || !$fields['imei'])) {
            $errors['imei'] = ['required' => $this->translator->trans('validation.errors.field.required')];
        }
        if ($fields['imei'] ?? null) {
            $deviceWithImei = $this->em->getRepository(Device::class)->findOneBy(['imei' => $fields['imei']]);
            if ($deviceWithImei && (!$device || ($device->getId() !== $deviceWithImei->getId()))) {
                $errors['imei'] = ['required' => $this->translator->trans('entities.device.device_imei')];
            }
        }

        if ($fields['status'] ?? null) {
            if (!in_array($fields['status'], Device::ALLOWED_STATUSES)) {
                $errors['status'] = ['required' => $this->translator->trans('validation.errors.field.wrong_value')];
            }
        }

        if ($fields['usage'] ?? null) {
            if (!in_array($fields['usage'], Device::ALLOWED_USAGE)) {
                $errors['usage'] = ['required' => $this->translator->trans('validation.errors.field.wrong_value')];
            }
        }

        if ($device && ($fields['isDeactivated'] ?? null) && !$device->getIsDeactivated() && $device->isActiveContract()
            && (!array_key_exists('contractFinishAt', $fields)
                || (array_key_exists('contractFinishAt', $fields)
                    && (new \DateTime($fields['contractFinishAt'])) > new \DateTime()))) {
            $errors['isDeactivated'] = ['required' => $this->translator->trans('entities.device.contract')];
        }

        if ($fields['ownership'] ?? null) {
            if (!in_array($fields['ownership'], Device::getAllowedOwnerships())) {
                $errors['ownership'] = ['required' => $this->translator->trans('validation.errors.field.wrong_value')];
            }
        }

        if (($currentUser->isInAdminTeam() || $currentUser->isInResellerTeam()) && isset($fields['teamId'])) {
            $team = $this->em->getRepository(Team::class)->find($fields['teamId']);
            if (!$currentUser->isInstaller() && (!$team || !ClientService::checkTeamAccess($team, $currentUser))) {
                $errors['teamId'] = ['required' => $this->translator->trans('validation.errors.field.wrong_value')];
            }
        }

        if (count($errors)) {
            throw (new ValidationException())->setErrors($errors);
        }

        return $fields;
    }

    public function handleEditFields(Device $device, array $data, User $currentUser): Device
    {
        $prevDevice = clone $device;
        $data['updatedAt'] = new \DateTime();
        $device->setAttributes($data);
        if ($data['modelId'] ?? null) {
            $device->setModel($this->em->getRepository(DeviceModel::class)->find($data['modelId']));
        }
        $device->setUpdatedBy($currentUser);

        if (($currentUser->isControlAdmin() || $currentUser->isInResellerTeam())) {
            if (array_key_exists('contractFinishAt', $data)) {
                if (is_null($data['contractFinishAt'])) {
                    $device->setContractFinishAt(null);
                } elseif ($data['contractFinishAt']) {
                    $device->setContractFinishAt(new \DateTime($data['contractFinishAt']));
                }
            }

            if (array_key_exists('contractStartAt', $data)) {
                if (is_null($data['contractStartAt'])) {
                    $device->setContractStartAt(null);
                } elseif ($data['contractStartAt']) {
                    $device->setContractStartAt(new \DateTime($data['contractStartAt']));
                }
            }
        }


        if (!$currentUser->isInClientTeam() && ($data['teamId'] ?? null) && $prevDevice->getTeamId() !== $data['teamId']) {
            $team = $this->em->getRepository(Team::class)->find($data['teamId']);
            if (!$team) {
                throw new \Exception('There is no team with this id');
            }
            $device->setTeam($team);
            $device->recalculateContractDate();

            $this->eventDispatcher->dispatch(new DeviceContractChangedEvent($device), DeviceContractChangedEvent::NAME);
            $this->eventDispatcher->dispatch(new DeviceChangedTeamEvent($device), DeviceChangedTeamEvent::NAME);
        } elseif ($prevDevice->getContractFinishAt() !== $device->getContractFinishAt()) {
            $this->eventDispatcher->dispatch(new DeviceContractChangedEvent($device), DeviceContractChangedEvent::NAME);
        }

        if ($prevDevice->getPhone() !== $device->getPhone()) {
            $this->eventDispatcher->dispatch(new DevicePhoneChangedEvent($device), DevicePhoneChangedEvent::NAME);
        }

        if (($data['isUnavailable'] ?? null) && !$prevDevice->getIsUnavailable()) {
            $device->setIsUnavailable(true);

            if ($data['blockingMessage'] ?? null) {
                $device->setBlockingMessage($data['blockingMessage']);
            }

            $this->notificationDispatcher->dispatch(Event::DEVICE_UNAVAILABLE, $device);
            $this->eventDispatcher->dispatch(new DeviceUnavailableEvent($device), DeviceUnavailableEvent::NAME);

            if ($device->getDeviceInstallation() && $device->getVehicle()) {
                $vehicle = $device->getVehicle();
                $this->eventDispatcher->dispatch(
                    new DeviceUninstalledEvent($device->getDeviceInstallation(), $currentUser),
                    DeviceUninstalledEvent::NAME
                );
                $this->eventDispatcher->dispatch(new VehicleStatusChangedEvent($vehicle),
                    VehicleStatusChangedEvent::NAME);

                if ($device->isInStock()) {
                    $this->notificationDispatcher->dispatch(Event::DEVICE_IN_STOCK, $device);
                }
                if ($data[VehicleService::VEHICLE_ACTION] ?? null) {
                    $vehicle = VehicleService::handleUninstallAction(
                        $data[VehicleService::VEHICLE_ACTION], $vehicle, $currentUser);
                }
            }
        } elseif (isset($data['isUnavailable']) && $data['isUnavailable'] === false && $prevDevice->getIsUnavailable()) {
            $device->setIsUnavailable(false);
            $this->eventDispatcher->dispatch(new DeviceUnavailableEvent($device), DeviceUnavailableEvent::NAME);

            if (!$device->getDeviceInstallation() || !$device->getVehicle()) {
                $device->setStatus(Device::STATUS_IN_STOCK);
            }
        }

        if (($data['isDeactivated'] ?? null) && !$prevDevice->getIsDeactivated()) {
            $this->eventDispatcher->dispatch(new DeviceDeactivatedEvent($device), DeviceDeactivatedEvent::NAME);
            if ($device->getDeviceInstallation() && $device->getVehicle()) {
                $vehicle = $device->getVehicle();

                $this->eventDispatcher->dispatch(
                    new DeviceUninstalledEvent($device->getDeviceInstallation(), $currentUser),
                    DeviceUninstalledEvent::NAME
                );

                if ($data[VehicleService::VEHICLE_ACTION] ?? null) {
                    $vehicle = VehicleService::handleUninstallAction(
                        $data[VehicleService::VEHICLE_ACTION], $vehicle, $currentUser);
                }
            }
        } elseif (isset($data['isDeactivated']) && $data['isDeactivated'] === false && $prevDevice->getIsDeactivated()) {
            $this->eventDispatcher->dispatch(new DeviceDeactivatedEvent($device), DeviceDeactivatedEvent::NAME);
        }

        return $device;
    }

    public function handleCreateFields(Device $device, array $data, User $currentUser): Device
    {
        $device->setCreatedBy($currentUser);

        if ($currentUser->isInClientTeam()) {
            $device->setTeam($currentUser->getTeam());
        } else {
            $team = $data['teamId'] ? $this->em->getRepository(Team::class)->find($data['teamId']) : $currentUser->getTeam();
            $device->setTeam($team);
        }

        if (($currentUser->isControlAdmin() || $currentUser->isInResellerTeam()) && ($data['contractFinishAt'] ?? null)) {
            $device->setContractFinishAt(new \DateTime($data['contractFinishAt']));
        } else {
            $device->recalculateContractDate();
        }

        if (($currentUser->isControlAdmin() || $currentUser->isInResellerTeam()) && ($data['contractStartAt'] ?? null)) {
            $device->setContractStartAt(new \DateTime($data['contractStartAt']));
        }

        $device->setModel($this->em->getRepository(DeviceModel::class)->find($data['modelId']));

        if (!($data['usage'] ?? null)) {
            $device->setUsage($device->getModel()->getUsage());
        }
        if (isset($data['streamaxIntegrationId'])) {
            $streamaxIntegration = $this->em->getRepository(StreamaxIntegration::class)
                ->find($data['streamaxIntegrationId']);

            if ($streamaxIntegration) {
                $device->setStreamaxIntegration($streamaxIntegration);
            }
        }

        $this->em->persist($device);

        if ($data['isUnavailable'] ?? null) {
            $device->setIsUnavailable(true);
            if ($data['blockingMessage'] ?? null) {
                $device->setBlockingMessage($data['blockingMessage']);
            }

            $this->eventDispatcher->dispatch(new DeviceUnavailableEvent($device), DeviceUnavailableEvent::NAME);
        }

        $this->eventDispatcher->dispatch(new DeviceContractChangedEvent($device), DeviceContractChangedEvent::NAME);
        $this->eventDispatcher->dispatch(new DeviceCreatedEvent($device), DeviceCreatedEvent::NAME);
        $this->eventDispatcher->dispatch(new DeviceChangedTeamEvent($device), DeviceChangedTeamEvent::NAME);

        return $device;
    }

    /**
     * @param Device $device
     * @param Device $deviceNew
     * @param User $currentUser
     * @return void
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function createNotesDuringDeviceReplacement(Device $device, Device $deviceNew, User $currentUser)
    {
        $this->noteService->create(
            [
                'note' => $this->translator->trans('entities.device.device_was_replaced_by', [
                    '%imei%' => $deviceNew->getImei()
                ]),
                'device' => $device,
                'noteType' => Note::TYPE_ADMIN,
                'createdBy' => $currentUser
            ]
        );
        $this->noteService->create(
            [
                'note' => $this->translator->trans('entities.device.device_replaced', [
                    '%imei%' => $device->getImei()
                ]),
                'device' => $deviceNew,
                'noteType' => Note::TYPE_ADMIN,
                'createdBy' => $currentUser
            ]
        );
    }
}