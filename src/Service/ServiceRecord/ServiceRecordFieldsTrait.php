<?php


namespace App\Service\ServiceRecord;


use App\Entity\Asset;
use App\Entity\ReminderCategory;
use App\Entity\ServiceRecord;
use App\Entity\User;
use App\Entity\Vehicle;
use App\Exceptions\ValidationException;
use App\Service\Client\ClientService;

trait ServiceRecordFieldsTrait
{
    /**
     * @param array $fields
     * @param User $currentUser
     * @throws ValidationException
     */
    private function validateServiceRecordFields(array $fields, User $currentUser)
    {
        $errors = [];

        if (!isset($fields['reminder'])
            || !ClientService::checkTeamAccess($fields['reminder']->getEntity()->getTeam(), $currentUser)) {
            $errors['reminder'] = ['required' => $this->translator->trans('validation.errors.field.required')];
        }

        if (isset($fields['date']) && $fields['date']) {
            $errors = $this->validationService->validateDate($fields, 'date', $errors);
        }

        if (count($errors)) {
            throw (new ValidationException())->setErrors($errors);
        }
    }

    /**
     * @param array $fields
     * @param User $currentUser
     * @throws ValidationException
     */
    private function validateRepairFields(array $fields, User $currentUser)
    {
        $errors = [];

        if ($fields['vehicleId'] ?? null) {
            $vehicle = $this->em->getRepository(Vehicle::class)->find($fields['vehicleId']);
            if (!$vehicle || !ClientService::checkTeamAccess($vehicle->getTeam(), $currentUser)) {
                $errors['vehicle'] = ['required' => $this->translator->trans('validation.errors.field.required')];
            }
        }

        if ($fields['assetId'] ?? null) {
            $asset = $this->em->getRepository(Asset::class)->find($fields['assetId']);
            if (!$asset || !ClientService::checkTeamAccess($asset->getTeam(), $currentUser)) {
                $errors['asset'] = ['required' => $this->translator->trans('validation.errors.field.wrong_value')];
            }
        }

        if ($fields['date'] ?? null) {
            $errors = $this->validationService->validateDate($fields, 'date', $errors);
        }

        if (count($errors)) {
            throw (new ValidationException())->setErrors($errors);
        }
    }

    private function handleCreateRepairFields(array $data, ServiceRecord $serviceRecord, User $currentUser)
    {
        $serviceRecord->setCreatedBy($currentUser);

        if ($data['title'] ?? null) {
            $serviceRecord->setRepairTitle($data['title']);
        }

        if ($data['categoryId'] ?? null) {
            $category = $this->em->getRepository(ReminderCategory::class)->find($data['categoryId']);
            if ($category) {
                $serviceRecord->setRepairCategory($category);
            }
        }

        if ($data['vehicleId'] ?? null) {
            $vehicle = $this->em->getRepository(Vehicle::class)->getVehicleById($currentUser, $data['vehicleId']);
            if ($vehicle) {
                $serviceRecord->setRepairVehicle($vehicle);
            }
        }

        if ($data['assetId'] ?? null) {
            $asset = $this->em->getRepository(Asset::class)->find($data['assetId']);
            if ($asset) {
                $serviceRecord->setRepairAsset($asset);
            }
        }

        if ($data['date']) {
            $serviceRecord->setDate(self::parseDateToUTC($data['date']));
        } else {
            $serviceRecord->setDate(null);
        }

        return $serviceRecord;
    }

    private function handleEditRepairFields(array $data, ServiceRecord $serviceRecord, User $currentUser)
    {
        $data['updatedAt'] = new \DateTime();
        $data['updatedBy'] = $currentUser;

        $serviceRecord->setAttributes($data);

        if ($data['date'] ?? null) {
            $serviceRecord->setDate(self::parseDateToUTC($data['date']));
        }

        $serviceRecord = $this->removeFiles($data, $serviceRecord);

        $serviceRecord = $this->addFiles($data, $serviceRecord, $currentUser);

        if ($data['title'] ?? null) {
            $serviceRecord->setRepairTitle($data['title']);
        }

        if ($data['categoryId'] ?? null) {
            $category = $this->em->getRepository(ReminderCategory::class)->find($data['categoryId']);
            if ($category) {
                $serviceRecord->setRepairCategory($category);
            }
        }

        return $serviceRecord;
    }
}