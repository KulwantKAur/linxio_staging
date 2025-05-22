<?php


namespace App\Service\Reminder;


use App\Entity\Asset;
use App\Entity\Reminder;
use App\Entity\ReminderCategory;
use App\Entity\ServiceRecord;
use App\Entity\User;
use App\Entity\Vehicle;
use App\Exceptions\ValidationException;
use App\Service\Client\ClientService;
use App\Util\ArrayHelper;
use Symfony\Component\HttpFoundation\FileBag;

trait ReminderFieldsTrait
{
    /**
     * @param array $fields
     * @param User $currentUser
     * @throws ValidationException
     */
    private function validateReminderFields(array $fields, User $currentUser)
    {
        $errors = [];

        if ($fields['vehicleId'] ?? null) {
            $vehicle = $this->em->getRepository(Vehicle::class)->find($fields['vehicleId']);
            if (!$vehicle || !ClientService::checkTeamAccess($vehicle->getTeam(), $currentUser)) {
                $errors['vehicleId'] = ['required' => $this->translator->trans('validation.errors.field.wrong_value')];
            }
        }

        if ($fields['assetId'] ?? null) {
            $asset = $this->em->getRepository(Asset::class)->find($fields['assetId']);
            if (!$asset || !ClientService::checkTeamAccess($asset->getTeam(), $currentUser)) {
                $errors['asset'] = ['required' => $this->translator->trans('validation.errors.field.wrong_value')];
            }
        }

        if ($fields['category'] ?? null) {
            $category = $this->em->getRepository(ReminderCategory::class)->find($fields['category']);
            if (!$category) {
                $errors['category'] = ['required' => $this->translator->trans('validation.errors.field.wrong_value')];
            }
        }

        if (!($fields['title'] ?? null)) {
            $errors['title'] = ['required' => $this->translator->trans('validation.errors.field.required')];
        }

        if ($fields['status'] ?? null) {
            if (!in_array($fields['status'], Reminder::ALLOWED_STATUSES)) {
                $errors['status'] = ['required' => $this->translator->trans('validation.errors.field.wrong_value')];
            }
        }

        if (count($errors)) {
            throw (new ValidationException())->setErrors($errors);
        }
    }

    private function handleCreateFields(array $data, Reminder $reminder, User $currentUser): Reminder
    {
        if ($data['vehicleId'] ?? null) {
            $vehicle = $this->em->getRepository(Vehicle::class)->getVehicleById($currentUser, $data['vehicleId']);
            if ($vehicle) {
                $reminder->setVehicle($vehicle);
            }
        }

        if ($data['assetId'] ?? null) {
            $asset = $this->em->getRepository(Asset::class)->find($data['assetId']);
            if ($asset) {
                $reminder->setAsset($asset);
            }
        }

        if ($data['date'] ?? null) {
            $reminder->setDate(self::parseDateToUTC($data['date']));
        }

        if ($data['category'] ?? null) {
            $category = $this->em->getRepository(ReminderCategory::class)->find($data['category']);
            $reminder->setCategory($category);
        }

        return $reminder;
    }

    private function handleCreateDraftField(array $data, Reminder $reminder, User $currentUser)
    {
        if (isset($data['draft']) && $data['draft']) {
            $draftFiles = new FileBag($data['files']->get('draft') ?? []);
            $this->serviceRecordService->create(
                array_merge(
                    $data['draft'],
                    ['status' => ServiceRecord::STATUS_DRAFT],
                    ['files' => $draftFiles]
                ),
                $currentUser,
                $reminder
            );
        }
    }

    private function handleEditDraftField(array $data, Reminder $reminder, User $currentUser): Reminder
    {
        if (isset($data['draft']) && $data['draft']) {
            $draftFiles = new FileBag($data['files']->get('draft') ?? []);
            $draftRecord = $reminder->getDraftRecord();
            if ($draftRecord) {
                $data['draft']['date'] =
                    ArrayHelper::getValueFromArray($data['draft'], 'date')
                        ? self::parseDateToUTC($data['draft']['date'])
                        : null;
                $draftRecord = $this->serviceRecordService->edit(
                    array_merge(
                        $data['draft'],
                        ['files' => $draftFiles]
                    ),
                    $currentUser,
                    $draftRecord
                );
            } else {
                $this->serviceRecordService->create(
                    array_merge(
                        $data['draft'],
                        ['status' => ServiceRecord::STATUS_DRAFT],
                        ['files' => $draftFiles]
                    ),
                    $currentUser,
                    $reminder
                );
            }
        } else {
            $draftRecord = $reminder->getDraftRecord();
            if ($draftRecord) {
                $reminder->removeServiceRecord($draftRecord);
                $this->em->remove($draftRecord);
            }
        }

        return $reminder;
    }

    public static function handleElasticArrayParams(array $params)
    {
        if (isset($params['defaultLabel']) && is_array($params['defaultLabel'])) {
            $params['caseOr']['vehicle.defaultLabel'] = $params['defaultLabel'];
            unset($params['defaultLabel']);
        }

        return $params;
    }
}