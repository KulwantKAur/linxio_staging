<?php

namespace App\Service\Notification;

use App\Entity\Area;
use App\Entity\AreaGroup;
use App\Entity\Depot;
use App\Entity\Notification\Event;
use App\Entity\Notification\Message;
use App\Entity\Notification\Notification;
use App\Entity\Notification\NotificationRecipients;
use App\Entity\Notification\ScopeType;
use App\Entity\Notification\Transport;
use App\Entity\Role;
use App\Entity\Setting;
use App\Entity\Team;
use App\Entity\User;
use App\Entity\UserGroup;
use App\Entity\Vehicle;
use App\Exceptions\ValidationException;
use App\Service\BaseService;
use App\Service\ElasticSearch\ElasticSearch;
use App\Service\Notification\DefaultNotification\ChevronDefaultNotification;
use App\Service\Validation\ValidationService;
use App\Util\StringHelper;
use Doctrine\ORM\EntityManager;
use FOS\ElasticaBundle\Finder\TransformedFinder;
use Symfony\Contracts\Translation\TranslatorInterface;

class NotificationService extends BaseService
{
    public const ELASTIC_NESTED_FIELDS = [];
    public const ELASTIC_SIMPLE_FIELDS = [
        'title' => 'title',
        'status' => 'status',
        'importance' => 'importance',
        'eventName' => 'event.name',
        'eventId' => 'event.id',
        'transportType' => 'transportsArray',
        'ownerTeamId' => 'ownerTeam.id',
        'day' => 'eventTrackingDays',
        'time' => 'timeFromTo'
    ];
    public const ELASTIC_RANGE_FIELDS = [];

    private $notificationRepository;
    private $eventRepository;
    private $teamRepository;

    public function __construct(
        private readonly TranslatorInterface $translator,
        private readonly EntityManager $em,
        private readonly TransformedFinder $notificationFinder,
        private readonly ValidationService $validationService
    ) {
        $this->notificationRepository = $em->getRepository(Notification::class);
        $this->eventRepository = $em->getRepository(Event::class);
        $this->teamRepository = $em->getRepository(Team::class);
    }

    public function getById(int $id, User $user): ?Notification
    {
        return $this->notificationRepository->findOneBy(
            [
                'id' => $id,
                'ownerTeam' => $user->getTeam(),
                'status' => Notification::ALL_STATUSES,
            ]
        );
    }

    public function notificationList(array $params, Team $team, bool $paginated = true): array
    {
        if (isset($params['status'])) {
            $params['status'] = $params['status'] === Notification::STATUS_ALL
                ? Notification::ALL_STATUSES
                : $params['status'];
        } else {
            $params['status'] = Notification::ALLOWED_STATUSES;
        }

        $fields = $this->prepareElasticFields(\array_merge($params, ['ownerTeamId' => $team->getId()]));

        $result = (new ElasticSearch($this->notificationFinder))->find(
            $fields,
            $fields['_source'] ?: Notification::DEFAULT_LISTING_DISPLAY_VALUES,
            $paginated
        );

        if (!$paginated) {
            return $result;
        }

        $result['data'] = array_map(
            function ($notification) {
                if (isset($notification['scope']['value']) && !empty($notification['scope']['value'])) {
                    $notification['scope']['value'] = $this->getScopeArrayValue($notification['scope']);
                }

                if (isset($notification['recipients']) && !empty($notification['recipients'])) {
                    $notification['recipients'] = array_map(
                        function ($recipient) {
                            $recipient['value'] = array_filter($recipient['value']);
                            if (!empty($recipient['value'])) {
                                $recipient['value'] = $this->getRecipientArrayValue($recipient);
                            }

                            return $recipient;
                        },
                        $notification['recipients']
                    );
                }

                return $notification;
            },
            $result['data']
        );

        return $result;
    }

    /**
     * @param array $recipient
     * @return array|mixed
     */
    private function getRecipientArrayValue(array $recipient)
    {
        return match ($recipient['type']) {
            NotificationRecipients::TYPE_USERS_LIST => \array_map(
                static function (User $u) {
                    return $u->toArray(['name', 'surname']);
                },
                $this->em->getRepository(User::class)->findBy(['id' => $recipient['value']], ['id' => 'ASC'])
            ),
            NotificationRecipients::TYPE_ROLE => \array_map(
                static function (Role $r) {
                    return $r->toArray(['team', 'displayName']);
                },
                $this->em->getRepository(Role::class)->findBy(['id' => $recipient['value']], ['id' => 'ASC'])
            ),
            NotificationRecipients::TYPE_USER_GROUPS_LIST => \array_map(
                static function (UserGroup $u) {
                    return $u->toArray(['name']);
                },
                $this->em->getRepository(UserGroup::class)->findBy(['id' => $recipient['value']], ['id' => 'ASC'])
            ),
            default => $recipient['value'],
        };
    }

    /**
     * @param array $scope
     * @return array|mixed
     */
    private function getScopeArrayValue(array $scope)
    {
        return match ($scope['subtype']) {
            ScopeType::SUBTYPE_USER => \array_map(
                static function (User $u) {
                    return $u->toArray(['name', 'surname']);
                },
                $this->em->getRepository(User::class)->findBy(['id' => $scope['value']], ['id' => 'ASC'])
            ),
            ScopeType::SUBTYPE_TEAM => \array_map(
                static function (Team $t) {
                    return $t->toArray(['clientName']);
                },
                $this->em->getRepository(Team::class)->findBy(['id' => $scope['value']], ['id' => 'ASC'])
            ),
            ScopeType::SUBTYPE_ROLE => \array_map(
                static function (Role $r) {
                    return $r->toArray(['team', 'displayName']);
                },
                $this->em->getRepository(Role::class)->findBy(['id' => $scope['value']], ['id' => 'ASC'])
            ),
            ScopeType::SUBTYPE_VEHICLE => \array_map(
                static function (Vehicle $v) {
                    return $v->toArray(['type', 'model', 'regNo', 'defaultLabel', 'year']);
                },
                $this->em->getRepository(Vehicle::class)->findBy(['id' => $scope['value']], ['id' => 'ASC'])
            ),
            ScopeType::SUBTYPE_DEPOT => \array_map(
                static function (Depot $d) {
                    return $d->toArray(['name', 'team']);
                },
                $this->em->getRepository(Depot::class)->findBy(['id' => $scope['value']], ['id' => 'ASC'])
            ),
            ScopeType::SUBTYPE_GROUP => \array_map(
                static function (Depot $d) {
                    return $d->toArray(['name', 'team']);
                },
                $this->em->getRepository(Depot::class)->findBy(['id' => $scope['value']], ['id' => 'ASC'])
            ),
            ScopeType::SUBTYPE_AREA => \array_map(
                static function (Area $d) {
                    return $d->toArray(['name', 'team']);
                },
                $this->em->getRepository(Area::class)->findBy(['id' => $scope['value']], ['id' => 'ASC'])
            ),
            ScopeType::SUBTYPE_AREAS_GROUP => \array_map(
                static function (AreaGroup $d) {
                    return $d->toArray(['name', 'team']);
                },
                $this->em->getRepository(AreaGroup::class)->findBy(['id' => $scope['value']], ['id' => 'ASC'])
            ),
            ScopeType::SUBTYPE_USER_GROUPS => \array_map(
                static function (UserGroup $d) {
                    return $d->toArray(['name', 'team']);
                },
                $this->em->getRepository(UserGroup::class)->findBy(['id' => $scope['value']], ['id' => 'ASC'])
            ),
            default => $scope['value'],
        };

    }

    public function create(array $data, ?User $currUser): Notification
    {
        $this->validateCreateNotificationFields($data, $currUser);

        $connection = $this->em->getConnection();
        try {
            $connection->beginTransaction();

            $notification = new Notification([]);
            $notification->setCreatedBy($currUser);

            $notification = $this->fillNotification($notification, $data, $currUser);

            $this->em->persist($notification);
            $this->em->flush();

            $connection->commit();

            return $notification;
        } catch (\Exception $e) {
            if ($connection->isTransactionActive()) {
                $connection->rollback();
            }
            throw $e;
        }
    }

    protected function validateCreateNotificationFields(array $fields, ?User $currUser): void
    {
        $errors = [];

        if (!isset($fields['title']) || !$fields['title']) {
            $errors['title'] = ['required' => $this->translator->trans('validation.errors.field.required')];
        }

        if (!isset($fields['status']) || !$fields['status']) {
            $errors['status'] = ['required' => $this->translator->trans('validation.errors.field.required')];
        } elseif (!in_array($fields['status'], Notification::ALLOWED_STATUSES, true)) {
            $errors['status'] = ['wrong_value' => $this->translator->trans('validation.errors.field.wrong_value')];
        }

        if (!isset($fields['importance']) || !$fields['importance']) {
            $errors['importance'] = ['required' => $this->translator->trans('validation.errors.field.required')];
        } elseif (!in_array($fields['importance'], Notification::ALLOWED_IMPORTANCE_TYPES, true)) {
            $errors['importance'] = ['wrong_value' => $this->translator->trans('validation.errors.field.wrong_value')];
        }

        if (!isset($fields['eventId']) || !$fields['eventId']) {
            $errors['eventId'] = ['required' => $this->translator->trans('validation.errors.field.required')];
        } elseif (null === $this->eventRepository->findOneBy(['id' => $fields['eventId'], 'type' => Event::TYPE_USER])
        ) {
            $errors['eventId'] = ['wrong_value' => $this->translator->trans('validation.errors.field.wrong_value')];
        }

        if (!isset($fields['scope']) || !$fields['scope']) {
            $errors['scope'] = ['required' => $this->translator->trans('validation.errors.field.required')];
        } elseif (!is_array($fields['scope'])) {
            $errors['scope'] = ['wrong_value' => $this->translator->trans('validation.errors.field.wrong_value')];
        } else {
            if (!isset($fields['scope']['subtype']) || !$fields['scope']['subtype']) {
                $errors['scope'] = ['required' => $this->translator->trans('validation.errors.field.required')];
            } elseif (null !== ($event = $this->eventRepository->find($fields['eventId']))) {
                if (!$event->scopeTypeAllowed(ScopeType::GENERAL_SCOPE_CATEGORY, $fields['scope']['subtype'])) {
                    $errors['scope.subtype'] = [
                        'required' => $this->translator->trans('validation.errors.field.required')
                    ];
                }
            }

            if ((!isset($fields['scope']['value']) || !$fields['scope']['value'])
                && in_array($fields['scope']['value'] ?? null, ScopeType::SUBTYPES_WITHOUT_VALUE, true)
            ) {
                $errors['scope.value'] = ['required' => $this->translator->trans('validation.errors.field.required')];
            }
            //Todo: Check Scope Value by role, user_ids and etc.?
        }

        if (!isset($fields['recipients']) || !$fields['recipients']) {
            $errors['recipients'] = ['required' => $this->translator->trans('validation.errors.field.required')];
        } elseif (!is_array($fields['recipients'])) {
            $errors['recipients'] = ['wrong_value' => $this->translator->trans('validation.errors.field.wrong_value')];
        } elseif (0 === count($fields['recipients'])) {
            $errors['recipients'] = ['wrong_value' => $this->translator->trans('validation.errors.field.wrong_value')];
        } else {
            foreach ($fields['recipients'] as $key => $recipient) {
                if (!isset($recipient['type']) || !$recipient['type']) {
                    $errors[sprintf('recipients.%d.type', $key)] = [
                        'required' => $this->translator->trans('validation.errors.field.required')
                    ];
                } elseif (!in_array($recipient['type'], NotificationRecipients::DISPLAY_TYPES, true)) {
                    $errors[sprintf('recipients.%d.type', $key)] = [
                        'wrong_value' => $this->translator->trans('validation.errors.field.wrong_value')
                    ];
                }

                if (!isset($recipient['value']) || !$recipient['value']) {
                    if (!is_array([
                        NotificationRecipients::TYPE_OTHER_EMAIL,
                        NotificationRecipients::TYPE_OTHER_PHONE
                    ])) {
                        $errors[sprintf('recipients.%d.value', $key)] = [
                            'required' => $this->translator->trans('validation.errors.field.required')
                        ];
                    }
                } elseif (!is_array($recipient['value'])) {
                    $errors[sprintf('recipients.%d.value', $key)] = [
                        'wrong_value' => $this->translator->trans('validation.errors.field.wrong_value')
                    ];
                } elseif (isset($recipient['type'])
                    && (count(array_filter($recipient['value'], 'is_numeric')) === count($recipient['value']))
                    && NotificationRecipients::TYPE_USERS_LIST === $recipient['type']) {
                    /** @var User[] $users */
                    $users = $this->em->getRepository(User::class)->findBy(['id' => $recipient['value']]);

                    /** Check Invalid ids */
                    if (count($users) !== count($recipient['value'])) {
                        $errors[sprintf('recipients.%d.value', $key)] = [
                            'wrong_value' => $this->translator->trans('validation.errors.field.wrong_value')
                        ];
                    } else {
                        /** Check Team permissions */
                        foreach ($users as $user) {
                            if ($currUser && $user->getTeam() !== $currUser->getTeam()) {
                                $errors[sprintf('recipients.%d.value', $key)] = [
                                    'wrong_value' => $this->translator->trans('validation.errors.field.wrong_value')
                                ];
                                break;
                            }
                        }
                    }
                } elseif ($currUser && isset($recipient['type'])
                    && (count(array_filter($recipient['value'], 'is_numeric')) === count($recipient['value']))
                    && NotificationRecipients::TYPE_ROLE === $recipient['type']) {
                    /** @var Role[] $roles */
                    $roles = $this->em->getRepository(Role::class)->findBy(
                        [
                            'id' => $recipient['value'],
                            'team' => $currUser->getTeam()->getType()
                        ]
                    );

                    if (count($roles) !== count($recipient['value'])) {
                        $errors[sprintf('recipients.%d.value', $key)] = [
                            'wrong_value' => $this->translator->trans('validation.errors.field.wrong_value')
                        ];
                    }
                } elseif ($currUser && isset($recipient['type'])
                    && (count(array_filter($recipient['value'], 'is_numeric')) === count($recipient['value']))
                    && NotificationRecipients::TYPE_USER_GROUPS_LIST === $recipient['type']
                ) {
                    /** @var UserGroup[] $userGroups */
                    $userGroups = $this->em->getRepository(UserGroup::class)->findBy(
                        [
                            'id' => $recipient['value'],
                            'team' => $currUser->getTeam()
                        ]
                    );

                    if (count($userGroups) !== count($recipient['value'])) {
                        $errors[sprintf('recipients.%d.value', $key)] = [
                            'wrong_value' => $this->translator->trans('validation.errors.field.wrong_value')
                        ];
                    }
                } elseif (isset($recipient['type'])
                    && NotificationRecipients::TYPE_OTHER_EMAIL === $recipient['type']
                ) {
                    $emails = $recipient['value'] ?? null;

                    if ($emails && count($emails) > 0) {
                        if (!$this->validateEmail($emails)) {
                            $errors[sprintf('recipients.%d.value', $key)] = [
                                'wrong_format' => $this->translator->trans('validation.errors.field.wrong_email_format')
                            ];
                        }
                    }
                } elseif (isset($recipient['type'])
                    && NotificationRecipients::TYPE_OTHER_PHONE === $recipient['type']
                ) {
                    $phones = $recipient['value'] ?? null;

                    if ($phones && count($phones) > 0) {
                        if (!$this->validatePhone($phones)) {
                            $errors[sprintf('recipients.%d.value', $key)] = [
                                'wrong_format' => $this->translator->trans('validation.errors.field.wrong_phone_format')
                            ];
                        }
                    }
                }
            }
        }

        if (!isset($fields['transports']) || !$fields['transports']) {
            $errors['transports'] = ['required' => $this->translator->trans('validation.errors.field.required')];
        } elseif (!is_array($fields['transports'])) {
            $errors['transports'] = ['wrong_value' => $this->translator->trans('validation.errors.field.wrong_value')];
        } elseif (0 === count($fields['transports'])) {
            $errors['transports'] = ['wrong_value' => $this->translator->trans('validation.errors.field.wrong_value')];
        } else {
            foreach ($fields['transports'] as $key => $transport) {
                if (!in_array($transport, Setting::ALLOWED_TRANSPORT_SETTINGS, true)) {
                    $errors[sprintf('transports.%d.alias', $key)] = [
                        'wrong_value' => $this->translator->trans('validation.errors.field.wrong_value')
                    ];
                }
            }
        }

        if (isset($fields['listenerTeamId']) && $fields['listenerTeamId']) {
            if (null === $this->teamRepository->find($fields['listenerTeamId'])) {
                $errors['listenerTeamId'] = [
                    'wrong_value' => $this->translator->trans('validation.errors.field.wrong_value')
                ];
            }
        }

        if (!isset($fields['eventTrackingTimeFrom']) || !$fields['eventTrackingTimeFrom']) {
            $errors['eventTrackingTimeFrom'] = [
                'required' => $this->translator->trans('validation.errors.field.required')
            ];
        } else {
            $errors = $this->validationService->validateTime($fields, 'eventTrackingTimeFrom', $errors, 'H:i');
        }

        if (!isset($fields['eventTrackingTimeUntil']) || !$fields['eventTrackingTimeUntil']) {
            $errors['eventTrackingTimeUntil'] = [
                'required' => $this->translator->trans('validation.errors.field.required')
            ];
        } else {
            $errors = $this->validationService->validateTime($fields, 'eventTrackingTimeUntil', $errors, 'H:i');
        }

        if (isset($fields['sendTimeFrom']) && $fields['sendTimeFrom']) {
            $errors = $this->validationService->validateTime($fields, 'sendTimeFrom', $errors, 'H:i');
        }

        if (isset($fields['sendTimeUntil']) && $fields['sendTimeUntil']) {
            $errors = $this->validationService->validateTime($fields, 'sendTimeUntil', $errors, 'H:i');
        }

        if (!isset($fields['eventTrackingDays']) || !$fields['eventTrackingDays']) {
            $errors['eventTrackingDays'] = [
                'required' => $this->translator->trans('validation.errors.field.required')
            ];
        } elseif (!is_array($fields['eventTrackingDays'])) {
            $errors['eventTrackingDays'] = [
                'wrong_value' => $this->translator->trans('validation.errors.field.wrong_value')
            ];
        } elseif (0 === count($fields['eventTrackingDays'])) {
            $errors['eventTrackingDays'] = [
                'wrong_value' => $this->translator->trans('validation.errors.field.wrong_value')
            ];
        } else {
            foreach ($fields['eventTrackingDays'] as $day) {
                if (!in_array($day, Notification::ALL_EVENT_TRACKING_DAYS, true)) {
                    $errors[sprintf('eventTrackingDays.%d', $day)] = [
                        'wrong_value' => $this->translator->trans('validation.errors.field.wrong_value')
                    ];
                }
            }
        }

        if (isset($fields['deviceVoltage']) && $fields['deviceVoltage']) {
            if (!is_numeric($fields['deviceVoltage'])) {
                $errors['deviceVoltage'] = [
                    'wrong_value' => $this->translator->trans('validation.errors.field.wrong_value')
                ];
            }
        }

        if (isset($fields['overSpeed']) && $fields['overSpeed']) {
            if (!is_numeric($fields['overSpeed'])) {
                $errors['overSpeed'] = [
                    'wrong_value' => $this->translator->trans('validation.errors.field.wrong_value')
                ];
            }
        }

        if (isset($fields['timeDuration']) && $fields['timeDuration']) {
            if (!is_numeric($fields['timeDuration'])) {
                $errors['timeDuration'] = [
                    'wrong_value' => $this->translator->trans('validation.errors.field.wrong_value')
                ];
            }
        }

        if (count($errors)) {
            throw (new ValidationException())->setErrors($errors);
        }
    }

    /**
     * @param Notification $notification
     * @param array $data
     * @param User $currUser
     * @return Notification
     * @throws ValidationException
     * @throws \Doctrine\DBAL\ConnectionException
     * @throws \Exception
     */
    public function edit(Notification $notification, array $data, User $currUser): Notification
    {
        $this->validateEditNotificationFields($data);

        $connection = $this->em->getConnection();
        try {
            $connection->beginTransaction();

            $notification
                ->setUpdatedBy($currUser)
                ->setUpdatedAt(new \DateTime());

            $notification = $this->fillNotification($notification, $data, $currUser);

            $this->em->persist($notification);
            $this->em->flush();

            $connection->commit();

            return $notification;
        } catch (\Exception $e) {
            $connection->rollback();
            throw $e;
        }
    }

    protected function fillNotification(Notification $notification, array $data, ?User $currUser): Notification
    {
        $data['listenerTeamId'] = $data['teamId'] ?? $currUser?->getTeam()->getId();

        $notification
            ->setTitle($data['title'] ?? $notification->getTitle())
            ->setStatus($data['status'] ?? $notification->getStatus())
            ->setImportance($data['importance'] ?? $notification->getImportance())
            ->setOwnerTeam($data['ownerTeam'] ?? $currUser?->getTeam())
            ->setEventTrackingTimeFrom($data['eventTrackingTimeFrom'] ?? Notification::DEFAULT_EVENT_TRACKING_TIME_FROM)
            ->setEventTrackingTimeUntil(
                $data['eventTrackingTimeUntil'] ?? Notification::DEFAULT_EVENT_TRACKING_TIME_UNTIL
            )
            ->setEventTrackingDays($data['eventTrackingDays'] ?? Notification::DEFAULT_EVENT_TRACKING_DAYS)
            ->setComment($data['comment'] ?? null)
            ->setAdditionalParams($data['additionalParams'] ?? null)
            ->setSendTimeFrom($data['sendTimeFrom'] ?? null)
            ->setSendTimeUntil($data['sendTimeUntil'] ?? null)
        ;

        if (isset($data['eventId'])) {
            $notification->setEvent($this->eventRepository->find($data['eventId']));
        }

        if (isset($data['listenerTeamId'])) {
            $notification->setListenerTeam($this->teamRepository->find($data['listenerTeamId']));
        }

        if (isset($data['scope'])) {
            $this->notificationRepository->fillScope(
                $notification,
                $data['scope'],
                ScopeType::GENERAL_SCOPE_CATEGORY
            );
        }

        if (isset($data['additionalScope'])) {
            $this->notificationRepository->fillScope(
                $notification,
                $data['additionalScope'],
                ScopeType::ADDITIONAL_SCOPE_CATEGORY
            );
        }

        if (isset($data['recipients'])) {
            $this->notificationRepository->fillRecipients($notification, $data['recipients']);
        }

        if (isset($data['acknowledgeRecipients'])) {
            $this->notificationRepository->fillAcknowledgeRecipients($notification, $data['acknowledgeRecipients']);
        }

        if (isset($data['transports'])) {
            $this->notificationRepository->fillTransports($notification, $data['transports']);
        }

        return $notification;
    }

    /**
     * @param array $fields
     * @throws ValidationException
     */
    protected function validateEditNotificationFields(array $fields): void
    {
        $errors = [];

        if (isset($fields['title']) && !$fields['title']) {
            $errors['title'] = ['wrong_value' => $this->translator->trans('validation.errors.field.wrong_value')];
        }

        if (isset($fields['status']) && !in_array($fields['status'], Notification::ALLOWED_STATUSES, true)) {
            $errors['status'] = ['wrong_value' => $this->translator->trans('validation.errors.field.wrong_value')];
        }

        if (isset($fields['importance'])
            && !in_array($fields['importance'], Notification::ALLOWED_IMPORTANCE_TYPES, true)
        ) {
            $errors['importance'] = ['wrong_value' => $this->translator->trans('validation.errors.field.wrong_value')];
        }

        if (isset($fields['eventId'])
            && null === $this->eventRepository->findOneBy(['id' => $fields['eventId'], 'type' => Event::TYPE_USER])
        ) {
            $errors['eventId'] = ['wrong_value' => $this->translator->trans('validation.errors.field.wrong_value')];
        }

        if (isset($fields['scope'])) {
            if (!is_array($fields['scope'])) {
                $errors['scope'] = ['wrong_value' => $this->translator->trans('validation.errors.field.wrong_value')];
            } else {
                if (!isset($fields['scope']['subtype']) || !$fields['scope']['subtype']) {
                    $errors['scope'] = ['required' => $this->translator->trans('validation.errors.field.required')];
                } elseif (null !== ($event = $this->eventRepository->find($fields['eventId']))) {
                    if (!$event->scopeTypeAllowed(ScopeType::GENERAL_SCOPE_CATEGORY, $fields['scope']['subtype'])) {
                        $errors['scope.subtype'] = [
                            'required' => $this->translator->trans('validation.errors.field.required')
                        ];
                    }
                }

                if ((!isset($fields['scope']['value']) || !$fields['scope']['value'])
                    && in_array('value', ScopeType::SUBTYPES_WITHOUT_VALUE, true)
                ) {
                    $errors['scope.value'] = [
                        'required' => $this->translator->trans('validation.errors.field.required')
                    ];
                }
                //Todo: Check Scope Value by role, user_ids and etc.?
            }
        }

        if (isset($fields['additionalScopes'])) {
            if (!is_array($fields['additionalScopes'])) {
                $errors['additionalScopes'] = [
                    'wrong_value' => $this->translator->trans(
                        'validation.errors.field.wrong_value'
                    )
                ];
            } else {
                if (!isset($fields['additionalScopes']['subtype']) || !$fields['additionalScopes']['subtype']) {
                    $errors['additionalScopes'] = [
                        'required' => $this->translator->trans(
                            'validation.errors.field.required'
                        )
                    ];
                } elseif (null !== ($event = $this->eventRepository->find($fields['eventId']))) {
                    if (!$event->scopeTypeAllowed(
                        ScopeType::ADDITIONAL_SCOPE_CATEGORY,
                        $fields['additionalScopes']['subtype']
                    )) {
                        $errors['additionalScopes.subtype'] = [
                            'required' => $this->translator->trans('validation.errors.field.required')
                        ];
                    }
                }

                if ((!isset($fields['additionalScopes']['value']) || !$fields['additionalScopes']['value'])
                    && in_array('value', ScopeType::SUBTYPES_WITHOUT_VALUE, true)
                ) {
                    $errors['additionalScopes.value'] = [
                        'required' => $this->translator->trans('validation.errors.field.required')
                    ];
                }
            }
        }

        if (isset($fields['recipients'])) {
            if (!is_array($fields['recipients'])) {
                $errors['recipients'] = [
                    'wrong_value' => $this->translator->trans('validation.errors.field.wrong_value')
                ];
            } elseif (0 === count($fields['recipients'])) {
                $errors['recipients'] = [
                    'wrong_value' => $this->translator->trans('validation.errors.field.wrong_value')
                ];
            } else {
                foreach ($fields['recipients'] as $key => $recipient) {
                    if (!isset($recipient['type']) || !$recipient['type']) {
                        $errors[sprintf('recipients.%d.type', $key)] = [
                            'required' => $this->translator->trans('validation.errors.field.required')
                        ];
                    } elseif (!in_array($recipient['type'], NotificationRecipients::DISPLAY_TYPES, true)) {
                        $errors[sprintf('recipients.%d.type', $key)] = [
                            'wrong_value' => $this->translator->trans('validation.errors.field.wrong_value')
                        ];
                    }

                    if (!isset($recipient['value']) || !$recipient['value']) {
                        if (!is_array([
                            NotificationRecipients::TYPE_OTHER_EMAIL,
                            NotificationRecipients::TYPE_OTHER_PHONE
                        ])) {
                            $errors[sprintf('recipients.%d.value', $key)] = [
                                'required' => $this->translator->trans('validation.errors.field.required')
                            ];
                        }
                    } elseif (!is_array($recipient['value'])) {
                        $errors[sprintf('recipients.%d.value', $key)] = [
                            'wrong_value' => $this->translator->trans('validation.errors.field.wrong_value')
                        ];
                    } elseif (isset($recipient['type'])
                        && NotificationRecipients::TYPE_OTHER_EMAIL === $recipient['type']
                    ) {
                        $emails = $recipient['value'] ?? null;

                        if ($emails && count($emails) > 0) {
                            if (!$this->validateEmail($emails)) {
                                $errors[sprintf('recipients.%d.value', $key)] = [
                                    'wrong_format' =>
                                        $this->translator->trans('validation.errors.field.wrong_email_format')
                                ];
                            }
                        }
                    } elseif (isset($recipient['type'])
                        && NotificationRecipients::TYPE_OTHER_PHONE === $recipient['type']
                    ) {
                        $phones = $recipient['value'] ?? null;

                        if ($phones && count($phones) > 0) {
                            if (!$this->validatePhone($phones)) {
                                $errors[sprintf('recipients.%d.value', $key)] = [
                                    'wrong_format' =>
                                        $this->translator->trans('validation.errors.field.wrong_phone_format')
                                ];
                            }
                        }
                    }
                }
                //Todo: Check Value by type?
            }
        }

        if (isset($fields['transports'])) {
            if (!is_array($fields['transports'])) {
                $errors['transports'] = [
                    'wrong_value' => $this->translator->trans('validation.errors.field.wrong_value')
                ];
            } elseif (0 === count($fields['transports'])) {
                $errors['transports'] = [
                    'wrong_value' => $this->translator->trans('validation.errors.field.wrong_value')
                ];
            } else {
                foreach ($fields['transports'] as $key => $transport) {
                    if (!in_array($transport, Setting::ALLOWED_TRANSPORT_SETTINGS, true)) {
                        $errors[sprintf('transports.%d.alias', $key)] = [
                            'wrong_value' => $this->translator->trans('validation.errors.field.wrong_value')
                        ];
                    }
                }
            }
        }

        if (isset($fields['listenerTeamId']) && $fields['listenerTeamId']) {
            if (null === $this->teamRepository->find($fields['listenerTeamId'])) {
                $errors['listenerTeamId'] = [
                    'wrong_value' => $this->translator->trans('validation.errors.field.wrong_value')
                ];
            }
        }

        if (count($errors)) {
            throw (new ValidationException())->setErrors($errors);
        }
    }

    public function delete(Notification $notification, User $user)
    {
        try {
            $notification->setStatus(Notification::STATUS_DELETED);
            $notification->setUpdatedAt(new \DateTime());
            $notification->setUpdatedBy($user);

            $this->em->flush();

            return $notification;
        } catch (\Exception $e) {
            throw $e;
        }
    }


    /**
     * @param string $phone
     *
     * This matches:
     * (+351) 280 40 25 50
     * 90000000001
     * 555-6789
     * 001 8765432
     * 1 (234) 567-8901
     * (123)8765432
     * (0011)(123)8765432
     *
     * @return bool
     */
    public function isValidPhone(string $phone): bool
    {
        $pattern = '/^(?:(?:\(?(?:00|\+)([1-4]\d\d|[1-9]\d?)\)?)?[\-\.\ \\\\\/]?)?((?:\(?\d{1,}\)?[\-\.\ \\\\\/]?){0,})?$/i';

        return preg_match($pattern, $phone);
    }

    /**
     * @param array $phones
     * @return bool
     */
    public function validatePhone(array $phones): bool
    {
        $isValid = true;
        foreach ($phones as $phone) {
            if (!empty($phone) && !$this->isValidPhone($phone)) {
                $isValid = false;
            }
        }

        return $isValid;
    }


    /**
     * @param array $emails
     * @return bool
     */
    public function validateEmail(array $emails): bool
    {
        $isValid = true;
        foreach ($emails as $email) {
            if (!empty($email) && !StringHelper::isValidEmail($email)) {
                $isValid = false;
            }
        }

        return $isValid;
    }

    public function initTeamDefaultNotification(Team $team): void
    {
        $role = $this->em->getRepository(Role::class)->findOneBy([
            'name' => Role::ROLE_CLIENT_ADMIN,
            'team' => Team::TEAM_CLIENT
        ]);
        $teamAdmin = $this->em->getRepository(User::class)
            ->findOneBy(['role' => $role, 'team' => $team]);
        $data = ChevronDefaultNotification::DATA;

        foreach ($data as $item) {
            $item['teamId'] = $team->getId();
            $item['ownerTeam'] = $team;
            $item['eventId'] = $this->em->getRepository(Event::class)
                ->getEventByName(Event::TRACKER_VOLTAGE)->getId();

            $this->create($item, $teamAdmin);
        }
    }
}
