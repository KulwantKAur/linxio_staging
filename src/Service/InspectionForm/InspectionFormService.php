<?php

namespace App\Service\InspectionForm;

use App\Entity\InspectionForm;
use App\Entity\InspectionFormData;
use App\Entity\InspectionFormDataValue;
use App\Entity\InspectionFormFile;
use App\Entity\Setting;
use App\Entity\Team;
use App\Entity\User;
use App\Entity\UserGroup;
use App\Entity\Vehicle;
use App\Exceptions\ValidationException;
use App\Service\BaseService;
use App\Service\ElasticSearch\ElasticSearch;
use App\Service\EntityHistory\EntityHistoryService;
use App\Service\File\FileService;
use App\Service\Notification\EventDispatcher as NotificationEventDispatcher;
use App\Service\Validation\ValidationService;
use Carbon\Carbon;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityNotFoundException;
use FOS\ElasticaBundle\Finder\TransformedFinder;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Contracts\Translation\TranslatorInterface;

class InspectionFormService extends BaseService
{
    protected $translator;
    private $em;
    private $inspectionFormFinder;
    private $eventDispatcher;
    private $validationService;
    private $fileService;
    private $entityHistoryService;
    private $notificationDispatcher;

    const ELASTIC_SIMPLE_FIELDS = [
        'id' => 'id',
        'vehicleId' => 'vehicle.id',
        'status' => 'status',
        'regNo' => 'vehicle.regNo',
        'fullName' => 'user.fullName',
        'performedBy' => 'user.fullName',
        'duration' => 'duration',
        'teamId' => 'team.id'
    ];

    public const ELASTIC_RANGE_FIELDS = [
        'date' => 'createdAt',
    ];

    /**
     * @param TranslatorInterface $translator
     * @param EntityManager $em
     * @param TransformedFinder $inspectionFormFinder
     * @param EventDispatcherInterface $eventDispatcher
     * @param ValidationService $validationService
     * @param FileService $fileService
     * @param EntityHistoryService $entityHistoryService
     * @param NotificationEventDispatcher $notificationDispatcher
     */
    public function __construct(
        TranslatorInterface $translator,
        EntityManager $em,
        TransformedFinder $inspectionFormFinder,
        EventDispatcherInterface $eventDispatcher,
        ValidationService $validationService,
        FileService $fileService,
        EntityHistoryService $entityHistoryService,
        NotificationEventDispatcher $notificationDispatcher
    ) {
        $this->translator = $translator;
        $this->em = $em;
        $this->inspectionFormFinder = $inspectionFormFinder;
        $this->eventDispatcher = $eventDispatcher;
        $this->validationService = $validationService;
        $this->fileService = $fileService;
        $this->entityHistoryService = $entityHistoryService;
        $this->notificationDispatcher = $notificationDispatcher;
    }

    /**
     * @param User $user
     * @param $vehicleId
     * @return InspectionForm|null
     * @throws \Exception
     */
    public function getForm(User $user, $vehicleId): ?InspectionForm
    {
        try {
            $inspectionFormSetting = $user->getTeam()->getSettingsByName(Setting::INSPECTION_FORM_PERIOD);

            if ($inspectionFormSetting && $vehicleId) {
                $vehicle = $this->em->getRepository(Vehicle::class)->getVehicleById($user, $vehicleId);

                switch ($inspectionFormSetting->getValue()) {
                    case Setting::INSPECTION_FORM_PERIOD_NEVER:
                        return null;
                    case Setting::INSPECTION_FORM_PERIOD_ONCE_PER_DAY:
                        if ($vehicle) {
                            $dateTime = Carbon::now($user->getTimezone())->startOfDay()->setTimezone('UTC');
                            $todayDataCount = $this->em->getRepository(InspectionFormData::class)
                                ->getInspectionFormCountDataFromDate(
                                    $vehicle,
                                    $dateTime
                                );
                            return !$todayDataCount
                                ? $this->getFirstInspectionForm($user->getTeam())
                                : null;
                        } else {
                            return $this->getFirstInspectionForm($user->getTeam());
                        }
                    case Setting::INSPECTION_FORM_PERIOD_EVERY_TIME:
                        return $this->getFirstInspectionForm($user->getTeam());
                }
            }

            return $this->getFirstInspectionForm($user->getTeam());
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * @param Team $team
     * @return InspectionForm|null
     */
    public function getFirstInspectionForm(Team $team): ?InspectionForm
    {
        return $this->em->getRepository(InspectionForm::class)->getFirstInspectionForm($team);
    }

    /**
     * @param $id
     * @param User $currentUser
     * @return InspectionFormData|null
     * @throws \Exception
     */
    public function getFilledForm($id, User $currentUser): ?InspectionFormData
    {
        try {
            return $this->em->getRepository(InspectionFormData::class)->getInspectionFormById($id, $currentUser);
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * @param User $currentUser
     * @param array $params
     * @return array|null
     * @throws \Exception
     */
    public function getFormList(User $currentUser, array $params): ?array
    {
        try {
            if ($currentUser->isInClientTeam()) {
                $params['teamId'] = [$currentUser->getTeam()->getId()];
            }
            if ($currentUser->isClientManager() && !$currentUser->isAllTeamsPermissions()) {
                $params['teamId'] = $currentUser->getManagedTeamsIds();
            }

            if ($currentUser->needToCheckUserGroup()) {
                $vehicleIds = $this->em->getRepository(UserGroup::class)->getUserVehiclesIdFromUserGroup($currentUser);
                if (isset($params['vehicleId'])) {
                    if (!in_array($params['vehicleId'], $vehicleIds)) {
                        $params['vehicleId'] = [];
                    }
                } else {
                    $params['vehicleId'] = $vehicleIds;
                }
            }

            $params['fields'] = isset($params['fields']) ? $params['fields'] : InspectionFormData::LIST_DISPLAY_VALUES;

            $fields = $this->prepareElasticFields($params);
            $elastica = new ElasticSearch($this->inspectionFormFinder);

            return $elastica->find($fields, $fields['_source'] ?? []);
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * @param int $formId
     * @param int $vehicleId
     * @param User $user
     * @param array $data
     * @return InspectionFormData
     * @throws \Doctrine\DBAL\ConnectionException
     */
    public function fillForm(int $formId, int $vehicleId, User $user, array $data, $files = null): InspectionFormData
    {
        $connection = $this->em->getConnection();

        try {
            $connection->beginTransaction();

            $this->validateFillParams($data);
            $form = $this->em->getRepository(InspectionForm::class)->find($formId);
            $vehicle = $this->em->getRepository(Vehicle::class)->getVehicleById($user, $vehicleId);
            if (!$form || !$vehicle) {
                throw new EntityNotFoundException();
            }

            $formData = new InspectionFormData(
                [
                    'form' => $form,
                    'user' => $user,
                    'vehicle' => $vehicle,
                    'version' => $form->getLastVersion()
                ]
            );

            $this->em->persist($formData);

            foreach ($form->getLastVersionTemplates() as $template) {
                $value = $this->getFieldValue($data, $template->getId(), 'value');
                $time = $this->getFieldValue($data, $template->getId(), 'time');
                $note = $this->getFieldValue($data, $template->getId(), 'note');
                $file = $this->getFieldValue($files, $template->getId(), 'file');
                if ($file) {
                    $fileEntity = $this->fileService->uploadInspectionFormFile($file, $user);
                } else {
                    unset($fileEntity);
                }

                $dataValue = new InspectionFormDataValue(
                    [
                        'formData' => $formData,
                        'formTemplate' => $template,
                        'value' => $value,
                        'time' => $time,
                        'note' => $note,
                        'file' => $fileEntity ?? null
                    ]
                );
                $this->em->persist($dataValue);
            }

            if (isset($files['files'])) {
                $files = $files['files'];
                if ($files['sign'] ?? null) {
                    $signFile = $this->uploadInspectionFile(
                        $files['sign'],
                        $user,
                        $formData,
                        InspectionFormFile::TYPE_SIGN
                    );
                    $this->em->persist($signFile);
                    $formData->addFile($signFile);
                    unset($files['sign']);
                }

                foreach ($files as $file) {
                    $ifFile = $this->uploadInspectionFile(
                        $file,
                        $user,
                        $formData
                    );
                    $formData->addFile($ifFile);
                }
            }

            $this->em->flush();
            $connection->commit();

            $this->em->refresh($formData);

            return $formData;
        } catch (\Exception $e) {
            $connection->rollback();
            throw $e;
        }
    }

    /**
     * @param $data
     * @param $templateId
     * @param $field
     * @return |null
     */
    private function getFieldValue($data, $templateId, $field)
    {
        return isset($data['fields'][$templateId][$field])
            ? $data['fields'][$templateId][$field]
            : null;
    }

    /**
     * @param array $params
     * @throws ValidationException
     */
    private function validateFillParams(array $params)
    {
        $errors = [];

        if (!($params['fields'] ?? null)) {
            $errors['fields'] = ['required' => $this->translator->trans('validation.errors.field.required')];
        }

        if (count($errors)) {
            throw (new ValidationException())->setErrors($errors);
        }
    }

    /**
     * @param UploadedFile $file
     * @param User $currentUser
     * @param InspectionFormData $formData
     * @param null $type
     * @return InspectionFormFile|UploadedFile
     */
    public function uploadInspectionFile(
        UploadedFile $file,
        User $currentUser,
        InspectionFormData $formData,
        $type = null
    ) {
        $fileEntity = $this->fileService->uploadInspectionFormFile($file, $currentUser);
        $file = new InspectionFormFile(
            [
                'file' => $fileEntity,
                'formData' => $formData,
                'type' => $type
            ]
        );

        return $file;
    }

    /**
     * @param Team $team
     * @param $formId
     * @return InspectionForm|object|null
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function setTeam(Team $team, $formId)
    {
        $form = $this->em->getRepository(InspectionForm::class)->find($formId);
        if ($form) {
            $form->addTeam($team);
            $this->em->flush();
        }

        return $form;
    }

    /**
     */
    public function getForms()
    {
        return $this->em->getRepository(InspectionForm::class)->findAll();
    }
}
