<?php

declare(strict_types = 1);

namespace App\Service\DigitalForm;

use App\Entity\Area;
use App\Entity\Depot;
use App\Entity\DigitalForm;
use App\Entity\DigitalFormSchedule;
use App\Entity\DigitalFormScheduleRecipient;
use App\Entity\Setting;
use App\Entity\Team;
use App\Entity\TimeZone;
use App\Entity\User;
use App\Entity\Vehicle;
use App\Entity\VehicleGroup;
use App\Events\DigitalForm\DigitalFormEvent;
use App\Exceptions\ValidationException;
use App\Repository\DigitalFormRepository;
use App\Repository\DigitalFormScheduleRecipientRepository;
use App\Repository\DigitalFormScheduleRepository;
use App\Service\BaseService;
use App\Util\StringHelper;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class DigitalFormScheduleService extends BaseService
{
    /** @inheritDoc */
    public const ELASTIC_SIMPLE_FIELDS = [
        'active' => 'digitalForm.active',
        'days' => 'days',
        'formId' => 'digitalForm.id',
        'id' => 'id',
        'status' => 'digitalForm.status',
        'teamId' => 'digitalForm.team.id',
        'timeFrom' => 'timeFrom',
        'timeTo' => 'timeTo',
        'title' => 'digitalForm.title',
        'inspectionPeriod' => 'digitalForm.inspectionPeriod',
        'type' => 'digitalForm.type',
        'userFullName' => 'createdBy.fullName',
        'userId' => 'createdBy.id',
        'weight' => 'weight',
    ];

    /** @inheritDoc */
    public const ELASTIC_RANGE_FIELDS = [
        'date' => 'createdAt',
        'vehicleCount' => 'vehicleCount',
    ];

    /** @var EntityManagerInterface */
    private $em;

    /** @var TranslatorInterface */
    private $translator;

    /** @var ElasticSearchSchedule */
    private $elasticSearch;

    /** @var EventDispatcherInterface */
    private $eventDispatcher;

    /** @var TokenStorageInterface */
    private $tokenStorage;


    /**
     * DigitalFormScheduleService constructor.
     * @param EntityManagerInterface $em
     * @param TranslatorInterface $translator
     * @param ElasticSearchSchedule $elasticSearch
     * @param EventDispatcherInterface $eventDispatcher
     * @param TokenStorageInterface $tokenStorage
     */
    public function __construct(
        EntityManagerInterface $em,
        TranslatorInterface $translator,
        ElasticSearchSchedule $elasticSearch,
        EventDispatcherInterface $eventDispatcher,
        TokenStorageInterface $tokenStorage
    ) {
        $this->em = $em;
        $this->translator = $translator;
        $this->elasticSearch = $elasticSearch;
        $this->eventDispatcher = $eventDispatcher;
        $this->tokenStorage = $tokenStorage;
    }

    /**
     * @param int $id
     * @return DigitalFormSchedule|null
     */
    public function getScheduleById(int $id): ?DigitalFormSchedule
    {
        /** @var DigitalFormScheduleRepository */
        $repo = $this->em->getRepository(DigitalFormSchedule::class);

        return $repo->getById($id);
    }

    /**
     * @param int $id
     * @return array
     */
    public function getSchedulesByFormId(int $id): array
    {
        /** @var DigitalFormScheduleRepository */
        $repo = $this->em->getRepository(DigitalFormSchedule::class);

        return $repo->getByFormId($id);
    }

    /**
     * @param User $user
     * @param array|null $withInspectionPeriods
     * @return DigitalForm|null
     */
    public function getInspectionForm(User $user, ?array $withInspectionPeriods = null): ?DigitalForm
    {
        $dt = $this->getDateTimeWithTimeZone($user->getTeam(), (new \DateTime()));

        $vehicle = $user->getVehicle();
        if ($vehicle === null) {
            return null;
        }

        /** @var DigitalFormRepository */
        $repo = $this->em->getRepository(DigitalForm::class);
        $formIds = $repo->getNotAnsweredInspectionFormIds($user, $dt, $withInspectionPeriods);
        if (empty($formIds)) {
            return null;
        }

        /** @var DigitalFormScheduleRepository */
        $repo = $this->em->getRepository(DigitalFormSchedule::class);
        $schedules = $repo->getByFormIds($formIds);
        if (empty($schedules)) {
            return null;
        }

        $schedule = $this->findOneAvailableSchedule(
            $schedules,
            $vehicle,
            $vehicle->getDepot(),
            $vehicle->getGroups(),
            $dt
        );
        if ($schedule === null) {
            return null;
        }

        $form = $schedule->getDigitalForm();
        $this->eventDispatcher->dispatch(new DigitalFormEvent($form, $vehicle), DigitalFormEvent::FORM_GET);

        return $form;
    }

    /**
     * @param User $user
     * @param array $params
     * @return array|null
     * @throws \Elastica\Exception\ElasticsearchException
     */
    public function getScheduleList(User $user, array $params): ?array
    {
        try {
            $params['teamId'] = $user->getTeamId();
            $params = $this->handleStatusParams($params);

            $fields = $this->prepareElasticFields($params);
            $data = $this->elasticSearch->find($fields, ['form', 'recipients']);

            return $this->handleVehicleCount($data);
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * @param Request $request
     * @param User $user
     * @param DigitalForm $form
     * @return DigitalFormSchedule
     * @throws ValidationException
     * @throws \Doctrine\DBAL\ConnectionException
     */
    public function createSchedule(Request $request, User $user, DigitalForm $form): DigitalFormSchedule
    {
        $weight = (int)$request->request->get('weight', DigitalFormSchedule::WEIGHT_DEFAULT);
        $days = $request->request->all('days') ?? [];
        $default = (bool)$request->request->get('default', false);
        $timeFrom = $request->request->get('timeFrom', null);
        $timeTo = $request->request->get('timeTo', null);
        $dayOfMonth = $request->request->get('dayOfMonth', null);
        $dayOfMonth = $dayOfMonth === null ? null : (int)$dayOfMonth;
        $this->validateSchedule($form, $weight, $days, $timeFrom, $timeTo, $dayOfMonth);

        $scopes = $request->request->all('scopes') ?? [];

        // if we have only ONE recipient
        if (!empty($scopes['type'])) {
            $scopes = [$scopes];
        }

        $this->validateScope($form, $scopes);

        try {
            $this->em->getConnection()->beginTransaction();

            $schedule = new DigitalFormSchedule();
            $schedule->setDigitalForm($form);
            $schedule->setIsDefault($default);
            $schedule->setWeight($weight);
            $schedule->setDays($days);
            if ($timeFrom) {
                $schedule->setTimeFrom(new \DateTime($timeFrom));
            }
            if ($timeTo) {
                $schedule->setTimeTo(new \DateTime($timeTo));
            }
            $schedule->setDayOfMonth($dayOfMonth);
            $schedule->setCreatedBy($user);
            $this->em->persist($schedule);

            foreach ($scopes as $scopeData) {
                $scope = new DigitalFormScheduleRecipient();
                $scope->setDigitalFormSchedule($schedule);
                $scope->setType((string)$scopeData['type']);
                $scope->setValue($scopeData['value'] ?? []);

                if (!empty($scopeData['additionalType'])) {
                    $scope->setAdditionalType((string)$scopeData['additionalType']);
                }
                if (!empty($scopeData['additionalValue'])) {
                    $scope->setAdditionalValue((array)$scopeData['additionalValue']);
                }

                $this->em->persist($scope);
            }

            $this->em->flush();
            $this->em->getConnection()->commit();
        } catch (\Exception $e) {
            $this->em->getConnection()->rollBack();
            throw $e;
        }

        return $schedule;
    }

    /**
     * @param User $user
     * @param DigitalForm $defaultForm
     * @param DigitalForm $form
     * @throws \Doctrine\DBAL\ConnectionException
     */
    public function createDefaultSchedule(User $user, DigitalForm $defaultForm, DigitalForm $form): void
    {
        $schedules = $this->getSchedulesByFormId($defaultForm->getId());
        /**
         * @var DigitalFormSchedule $defaultSchedule
         */
        $defaultSchedule = array_shift($schedules);

        try {
            $this->em->getConnection()->beginTransaction();

            $schedule = new DigitalFormSchedule();
            $schedule->setDigitalForm($form);
            $schedule->setIsDefault($defaultSchedule->getIsDefault());
            $schedule->setWeight($defaultSchedule->getWeight());
            $schedule->setDays($defaultSchedule->getDays());
            $schedule->setTimeFrom($defaultSchedule->getTimeFrom());
            $schedule->setTimeTo($defaultSchedule->getTimeTo());
            $schedule->setDayOfMonth($defaultSchedule->getDayOfMonth());
            $schedule->setCreatedBy($user);
            $this->em->persist($schedule);

            foreach ($defaultSchedule->getDigitalFormScheduleRecipients() as $recipient) {
                $item = new DigitalFormScheduleRecipient();
                $item->setDigitalFormSchedule($schedule);
                $item->setType($recipient->getType());
                $item->setValue($recipient->getValue());

                if (!empty($recipient->getAdditionalType())) {
                    $item->setAdditionalType($recipient->getAdditionalType());
                }
                if (!empty($recipient->getAdditionalValue())) {
                    $item->setAdditionalValue($recipient->getAdditionalValue());
                }

                $this->em->persist($item);
            }

            $this->em->flush();
            $this->em->getConnection()->commit();
        } catch (\Exception $e) {
            $this->em->getConnection()->rollBack();
            throw $e;
        }
    }

    /**
     * @return array[]
     */
    public function getScope(): array
    {
        return [
            [
                'id' => 1,
                'type' => DigitalForm::TYPE_INSPECTION,
                'scopes' => [
                    [
                        'name' => ucfirst(DigitalFormScheduleRecipient::TYPE_ANY),
                        'type' => DigitalFormScheduleRecipient::TYPE_ANY,
                    ],
                    [
                        'name' => ucfirst(DigitalFormScheduleRecipient::TYPE_DEPOT),
                        'type' => DigitalFormScheduleRecipient::TYPE_DEPOT,
                    ],
                    [
                        'name' => ucfirst(DigitalFormScheduleRecipient::TYPE_GROUP),
                        'type' => DigitalFormScheduleRecipient::TYPE_GROUP,
                    ],
                    [
                        'name' => ucfirst(DigitalFormScheduleRecipient::TYPE_VEHICLE),
                        'type' => DigitalFormScheduleRecipient::TYPE_VEHICLE,
                    ],
                ],
                'additionalScopes' => [
                    [
                        'name' => ucfirst(DigitalFormScheduleRecipient::TYPE_ANY),
                        'type' => DigitalFormScheduleRecipient::TYPE_ANY,
                    ],
                    [
                        'name' => ucfirst(DigitalFormScheduleRecipient::TYPE_AREA),
                        'type' => DigitalFormScheduleRecipient::TYPE_AREA,
                    ],
                ],
            ],
            [
                'id' => 2,
                'type' => DigitalForm::TYPE_MAINTENANCE,
                'scopes' => [
                    [
                        'name' => ucfirst(DigitalFormScheduleRecipient::TYPE_ANY),
                        'type' => DigitalFormScheduleRecipient::TYPE_ANY,
                    ],
                    [
                        'name' => ucfirst(DigitalFormScheduleRecipient::TYPE_DEPOT),
                        'type' => DigitalFormScheduleRecipient::TYPE_DEPOT,
                    ],
                    [
                        'name' => ucfirst(DigitalFormScheduleRecipient::TYPE_GROUP),
                        'type' => DigitalFormScheduleRecipient::TYPE_GROUP,
                    ],
                    [
                        'name' => ucfirst(DigitalFormScheduleRecipient::TYPE_VEHICLE),
                        'type' => DigitalFormScheduleRecipient::TYPE_VEHICLE,
                    ],
                ],
                'additionalScopes' => [
                    [
                        'name' => ucfirst(DigitalFormScheduleRecipient::TYPE_ANY),
                        'type' => DigitalFormScheduleRecipient::TYPE_ANY,
                    ],
                ],
            ],
            [
                'id' => 3,
                'type' => DigitalForm::TYPE_REPAIR_COST,
                'scopes' => [
                    [
                        'name' => ucfirst(DigitalFormScheduleRecipient::TYPE_ANY),
                        'type' => DigitalFormScheduleRecipient::TYPE_ANY,
                    ],
                    [
                        'name' => ucfirst(DigitalFormScheduleRecipient::TYPE_DEPOT),
                        'type' => DigitalFormScheduleRecipient::TYPE_DEPOT,
                    ],
                    [
                        'name' => ucfirst(DigitalFormScheduleRecipient::TYPE_GROUP),
                        'type' => DigitalFormScheduleRecipient::TYPE_GROUP,
                    ],
                    [
                        'name' => ucfirst(DigitalFormScheduleRecipient::TYPE_VEHICLE),
                        'type' => DigitalFormScheduleRecipient::TYPE_VEHICLE,
                    ],
                ],
                'additionalScopes' => [
                    [
                        'name' => ucfirst(DigitalFormScheduleRecipient::TYPE_ANY),
                        'type' => DigitalFormScheduleRecipient::TYPE_ANY,
                    ],
                ],
            ],
            [
                'id' => 4,
                'type' => DigitalForm::TYPE_AREA,
                'scopes' => [
                    [
                        'name' => ucfirst(DigitalFormScheduleRecipient::TYPE_ANY),
                        'type' => DigitalFormScheduleRecipient::TYPE_ANY,
                    ],
                    [
                        'name' => ucfirst(DigitalFormScheduleRecipient::TYPE_AREA),
                        'type' => DigitalFormScheduleRecipient::TYPE_AREA,
                    ],
                ],
                'additionalScopes' => [
                    [
                        'name' => ucfirst(DigitalFormScheduleRecipient::TYPE_ANY),
                        'type' => DigitalFormScheduleRecipient::TYPE_ANY,
                    ],
                ],
            ],
        ];
    }

    /**
     * @param string|null $type
     * @param array $value
     * @return int
     */
    public function getRecipientVehicleCount(string $type = null, array $value = []): int
    {
        $count = 0;
        switch ($type) {
            case DigitalFormScheduleRecipient::TYPE_VEHICLE:
                $count = count($value);
                break;
            case DigitalFormScheduleRecipient::TYPE_GROUP:
                foreach ($value as $id) {
                    $entity = $this->em->getRepository(VehicleGroup::class)->find($id);
                    $count += $entity ? $entity->getVehicleEntities()->count() : 0;
                }
                break;
            case DigitalFormScheduleRecipient::TYPE_DEPOT:
                foreach ($value as $id) {
                    $entity = $this->em->getRepository(Depot::class)->find($id);
                    $count += $entity ? $entity->getVehicles()->count() : 0;
                }
                break;
            case DigitalFormScheduleRecipient::TYPE_ANY:
                $token = $this->tokenStorage->getToken();
                if ($token && ($token->getUser() instanceof User)) {
                    $team = $token->getUser()->getTeam();
                    $count += $this->em->getRepository(Vehicle::class)->getVehicleCountByTeam($team);
                }
                break;
            default:
                break;
        }

        return $count;
    }

    /**
     * @param DigitalForm $form
     * @param int $weight
     * @param array $days
     * @param string|null $timeFrom
     * @param string|null $timeTo
     * @param int|null $dayOfMonth
     * @throws ValidationException
     */
    private function validateSchedule(
        DigitalForm $form,
        int $weight,
        array $days = [],
        string $timeFrom = null,
        string $timeTo = null,
        int $dayOfMonth = null
    ): void {
        //some field have to be empty in some cases
        switch ($form->getInspectionPeriod()) {
            case DigitalForm::INSPECTION_PERIOD_SHOW_ALWAYS:
            case DigitalForm::INSPECTION_PERIOD_ONCE_PER_DAY:
                $this->validateValuesAreEmpty(
                    [
                        'dayOfMonth' => $dayOfMonth
                    ]
                );
                break;
            case DigitalForm::INSPECTION_PERIOD_EVERY_TIME:
                $this->validateValuesAreEmpty(
                    [
                        'days' => $days,
                        'timeFrom' => $timeFrom,
                        'timeTo' => $timeTo,
                        'dayOfMonth' => $dayOfMonth
                    ]
                );
                break;
            case DigitalForm::INSPECTION_PERIOD_ONCE_PER_WEEK:
                $this->validateValuesAreEmpty(
                    [
                        'timeFrom' => $timeFrom,
                        'timeTo' => $timeTo,
                        'dayOfMonth' => $dayOfMonth
                    ]
                );
                break;
            case DigitalForm::INSPECTION_PERIOD_ONCE_PER_MONTH:
                $this->validateValuesAreEmpty(
                    [
                        'days' => $days,
                        'timeFrom' => $timeFrom,
                        'timeTo' => $timeTo
                    ]
                );
                break;
            default:
                throw new \Exception('Unknown form period: ' . $form->getInspectionPeriod());
        }

        $this->validateScheduleValues($weight, $days, $timeFrom, $timeTo, $dayOfMonth);
    }

    /**
     * @param int $weight
     * @param array $days
     * @param string|null $timeFrom
     * @param string|null $timeTo
     * @param int|null $dayOfMonth
     *
     * @throws ValidationException
     */
    private function validateScheduleValues(
        int $weight,
        array $days = [],
        string $timeFrom = null,
        string $timeTo = null,
        int $dayOfMonth = null
    ): void {
        $errors = [];
        if ($weight > 32767 || $weight < -32768) {
            $errors['weight'] = ['required' => $this->translator->trans('validation.errors.field.wrong_value')];
        }
        if (!empty($days) && (count(array_diff($days, DigitalFormSchedule::VALID_DAYS)) > 0)) {
            $errors['days'] = ['required' => $this->translator->trans('validation.errors.field.wrong_value')];
        }
        if (!empty($timeFrom)) {
            try {
                new \DateTime($timeFrom);
            } catch (\Throwable $e) {
                $errors['timeFrom'] = ['required' => $this->translator->trans('validation.errors.field.wrong_value')];
            }
        }

        if (!empty($timeTo)) {
            try {
                new \DateTime($timeTo);
            } catch (\Throwable $e) {
                $errors['timeTo'] = ['required' => $this->translator->trans('validation.errors.field.wrong_value')];
            }
        }

        if ($dayOfMonth !== null && ($dayOfMonth < 0 || $dayOfMonth > 31)) {
            $errors['dayOfMonth'] = ['required' => $this->translator->trans('validation.errors.field.wrong_value')];
        }

        if (count($errors)) {
            throw (new ValidationException())->setErrors($errors);
        }
    }

    /**
     * @param array $list
     *
     * @throws ValidationException
     */
    private function validateValuesAreEmpty(array $list): void
    {
        $errors = [];
        foreach ($list as $key => $value) {
            if (!empty($value)) {
                $errors[$key] = [
                    'required' => $this->translator->trans('validation.errors.field.should_be_empty')
                ];
            }
        }

        if (count($errors)) {
            throw (new ValidationException())->setErrors($errors);
        }
    }

    /**
     * @param DigitalForm $form
     * @param array $scopes
     *
     * @throws ValidationException
     */
    private function validateScope(DigitalForm $form, array $scopes): void
    {
        $errors = [];
        if (empty($scopes)) {
            $errors['scopes'] = ['required' => $this->translator->trans('validation.errors.field.wrong_value')];
        }

        foreach ($scopes as $key => $item) {
            if (empty($item['type']) || !in_array($item['type'], DigitalFormScheduleRecipient::VALID_TYPES)) {
                $errors['type-' . $key] = [
                    'required' => $this->translator->trans(
                        'validation.errors.field.wrong_value_for_name',
                        ['%name%' => 'scopes']
                    )
                ];
                continue;
            }

            $value = $item['value'] ?? [];
            if ($this->getScopeEntities((string)$item['type'], (array)$value) === null) {
                $errors['value-' . $key] = [
                    'required' => $this->translator->trans('validation.errors.field.wrong_value')
                ];
                continue;
            }
            if (!empty($item['additionalType'])) {
                $additionalValue = $item['additionalValue'] ?? [];
                if ($this->getScopeEntities((string)$item['additionalType'], (array)$additionalValue) === null) {
                    $errors['additionalValue-' . $key] = [
                        'required' => $this->translator->trans('validation.errors.field.wrong_value')
                    ];
                    continue;
                }
            }
        }

        if (count($errors)) {
            throw (new ValidationException())->setErrors($errors);
        }
    }

    /**
     * @param string $type
     * @param array $ids
     * @return array|null
     */
    private function getScopeEntities(string $type, array $ids): ?array
    {
        switch ($type) {
            case DigitalFormScheduleRecipient::TYPE_ANY:
                return [];
                break;
            case DigitalFormScheduleRecipient::TYPE_GROUP:
                $entities = $this->em->getRepository(VehicleGroup::class)->findBy(['id' => $ids]);
                break;
            case DigitalFormScheduleRecipient::TYPE_VEHICLE:
                $entities = $this->em->getRepository(Vehicle::class)->findBy(['id' => $ids]);
                break;
            case DigitalFormScheduleRecipient::TYPE_DEPOT:
                $entities = $this->em->getRepository(Depot::class)->findBy(['id' => $ids]);
                break;
            case DigitalFormScheduleRecipient::TYPE_AREA:
                $entities = $this->em->getRepository(Area::class)->findBy(['id' => $ids]);
                break;
            default:
                return null;
                break;
        }

        return count($entities) === count($ids) ? $entities : null;
    }

    /**
     * @param array $schedules
     * @param Vehicle $vehicle
     * @param Depot|null $depot
     * @param Collection $groups
     * @param \DateTime $datetime
     * @return DigitalFormSchedule|null
     * @throws \Exception
     */
    private function findOneAvailableSchedule(
        array $schedules,
        Vehicle $vehicle,
        ?Depot $depot,
        Collection $groups,
        \DateTime $datetime
    ): ?DigitalFormSchedule {
        /** @var DigitalFormScheduleRecipientRepository */
        $repo = $this->em->getRepository(DigitalFormScheduleRecipient::class);

        // try to find schedule based on recipient
        $recipients = $repo->getScheduleByParams($schedules, $vehicle, $depot, $groups);

        // try to find default schedule
        if (empty($recipients)) {
            foreach ($schedules as $schedule) {
                if ($schedule->getIsDefault() === true) {
                    return $schedule;
                }
            }

            return null;
        }

        // check days and time chedule availability
        foreach ($recipients as $recipient) {
            /** @var DigitalFormSchedule $schedule  */
            $schedule = $recipient->getDigitalFormSchedule();

            return $this->isScheduleAvailible($schedule, $datetime) ? $schedule : null;

            return $schedule;
        }

        return null;
    }

    /**
     * @param array $data
     * @return array
     */
    private function handleVehicleCount(array &$data): array
    {
        foreach ($data['data'] as &$row) {
            $type = (isset($row['recipients']) && $row['recipients']['type']) ? $row['recipients']['type'] : null;
            $value = (isset($row['recipients']) && $row['recipients']['value']) ? $row['recipients']['value'] : [];

            $row['vehicleCount'] = $this->getRecipientVehicleCount($type, $value);
        }

        return $data;
    }

    /**
     * @param array $params
     * @return array
     */
    private function handleStatusParams(array $params): array
    {
        $params['active'] = true;
        $params['status'] = isset($params['status']) ? [$params['status']] : DigitalForm::STATUS_ACTIVE_TYPES;

        $params['type'] = isset($params['type'])
            ? (in_array($params['type'], DigitalForm::VALID_TYPES)
                ? $params['type'] : DigitalForm::TYPE_INSPECTION)
            : DigitalForm::TYPE_INSPECTION;

        if (!empty($params['inspectionPeriod'])) {
            $params['inspectionPeriod'] =
                in_array($params['inspectionPeriod'], DigitalForm::INSPECTION_PERIOD_VALID_TYPES)
                    ? $params['inspectionPeriod'] : null;
        }


        if (isset($params['showDeleted']) && StringHelper::stringToBool($params['showDeleted'])) {
            if (is_array($params['status'])) {
                $params['status'][] = DigitalForm::STATUS_DELETED;
            } else {
                $status = $params['status'];
                $params['status'] = [$status, DigitalForm::STATUS_DELETED];
            }
        } elseif (is_array($params['status'])
            && ($key = array_search(DigitalForm::STATUS_DELETED, $params['status'])) !== false
        ) {
            unset($params['status'][$key]);
        } elseif (!is_array($params['status']) && $params['status'] === DigitalForm::STATUS_DELETED) {
            $params['status'] = '';
        }

        return $params;
    }


    /**
     * @param User $user
     * @return array|DigitalForm[]
     * @throws \Exception
     */
    public function getInspectionFormsList(User $user): array
    {
        $list = [];
        $dt = $this->getDateTimeWithTimeZone($user->getTeam(), (new \DateTime()));

        $vehicle = $user->getVehicle();
        if ($vehicle === null) {
            return $list;
        }

        /** @var DigitalFormRepository */
        $repo = $this->em->getRepository(DigitalForm::class);
        $formIds = $repo->getNotAnsweredInspectionFormIds($user, $dt);
        if (empty($formIds)) {
            return $list;
        }

        /** @var DigitalFormScheduleRepository */
        $repo = $this->em->getRepository(DigitalFormSchedule::class);
        $schedules = $repo->getByFormIds($formIds);
        if (empty($schedules)) {
            return $list;
        }

        $availableSchedules = $this->findAllAvailableSchedules(
            $schedules,
            $vehicle,
            $vehicle->getDepot(),
            $vehicle->getGroups(),
            $dt
        );
        foreach ($availableSchedules as $schedule) {
            $form = $schedule->getDigitalForm();
            $this->eventDispatcher->dispatch(new DigitalFormEvent($form, $vehicle), DigitalFormEvent::FORM_GET);

            $list[] = $form;
        }

        return $list;
    }

    /**
     * @param array $schedules
     * @param Vehicle $vehicle
     * @param Depot|null $depot
     * @param Collection $groups
     * @param \DateTime $datetime
     * @return array|DigitalFormSchedule[]
     * @throws \Exception
     */
    private function findAllAvailableSchedules(
        array $schedules,
        Vehicle $vehicle,
        ?Depot $depot,
        Collection $groups,
        \DateTime $datetime
    ): array {
        $list = [];

        /** @var DigitalFormScheduleRecipientRepository */
        $repo = $this->em->getRepository(DigitalFormScheduleRecipient::class);

        // try to find schedule based on recipient
        $recipients = $repo->getScheduleByParams($schedules, $vehicle, $depot, $groups);

        // try to find default schedule
        if (empty($recipients)) {
            foreach ($schedules as $schedule) {
                if ($schedule->getIsDefault() === true) {
                    $list[] = $schedule;
                }
            }

            return $list;
        }

        // check days and time chedule availability
        foreach ($recipients as $recipient) {
            $schedule = $recipient->getDigitalFormSchedule();

            if ($this->isScheduleAvailible($schedule, $datetime)) {
                $list[$schedule->getId()] = $schedule;
            }
        }

        return $list;
    }

    /**
     * checking of schedule availability according to inspection period
     *
     * @param DigitalFormSchedule $schedule
     * @param \DateTime $datetime
     *
     * @return bool
     *
     * @throws \Exception
     */
    public function isScheduleAvailible(DigitalFormSchedule $schedule, \DateTime $datetime): bool
    {
        // time checking
        if (
        in_array(
            $schedule->getDigitalForm()->getInspectionPeriod(),
            [
                DigitalForm::INSPECTION_PERIOD_SHOW_ALWAYS,
                DigitalForm::INSPECTION_PERIOD_ONCE_PER_DAY
            ]
        )
        ) {
            $timeFrom = new \Datetime($schedule->getTimeFrom()->format('H:i:s'));
            $timeTo = new \Datetime($schedule->getTimeTo()->format('H:i:s'));

            if (($datetime < $timeFrom) && ($datetime > $timeTo)) {
                return false;
            }
        }

        // day of week checking
        if (
            in_array(
                $schedule->getDigitalForm()->getInspectionPeriod(),
                [
                    DigitalForm::INSPECTION_PERIOD_SHOW_ALWAYS,
                    DigitalForm::INSPECTION_PERIOD_ONCE_PER_WEEK
                ]
            )
            && !in_array(strtolower($datetime->format('l')), $schedule->getDays())
        ) {
            return false;
        }

//        // day of month checking
//        if (
//            in_array(
//                $schedule->getDigitalForm()->getInspectionPeriod(),
//                [
//                    DigitalForm::INSPECTION_PERIOD_ONCE_PER_MONTH
//                ]
//            )
//            && (int)$datetime->format('d') < $schedule->getDayOfMonth()
//        ) {
//            return false;
//        }

        return true;
    }

    /**
     * @param Team $team
     * @param \DateTime $dt
     * @return \DateTime
     */
    public function getDateTimeWithTimeZone(Team $team, \DateTime $dt)
    {
        $dateTime = clone $dt;
        $timeZoneSetting = $team->getSettingsByName(Setting::TIMEZONE_SETTING);
        $timeZone = $timeZoneSetting
            ? $this->em->getRepository(TimeZone::class)->find($timeZoneSetting->getValue())
            : null;
        if ($timeZoneSetting && $timeZone) {
            $dateTime->setTimezone(new \DateTimeZone($timeZone->getName()));
        }

        return $dateTime;
    }
}
