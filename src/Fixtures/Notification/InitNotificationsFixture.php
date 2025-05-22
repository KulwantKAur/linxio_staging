<?php

namespace App\Fixtures\Notification;

use App\Entity\Notification\Event;
use App\Entity\Notification\Notification;
use App\Entity\Notification\NotificationRecipients;
use App\Entity\Notification\NotificationScopes;
use App\Entity\Notification\NotificationTransports;
use App\Entity\Notification\ScopeType;
use App\Entity\Notification\Transport;
use App\Entity\Role;
use App\Entity\Team;
use App\Entity\User;
use App\Fixtures\BaseFixture;
use App\Fixtures\FixturesTypes;
use App\Fixtures\Teams\InitTeamsFixture;
use App\Fixtures\Users\InitDemoUsersFixtures;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Doctrine\ORM\EntityManager;

class InitNotificationsFixture extends BaseFixture implements DependentFixtureInterface, FixtureGroupInterface
{
    public const DATA = [
        [
            'title' => '[' .Event::USER_CREATED. '] System Notification with invite',
            'status' => Notification::STATUS_DISABLED,
            'event' => [Event::USER_CREATED_SYSTEM, Event::TYPE_SYSTEM],
            'listenerTeam' => null,
            'ownerTeam' => null,
            'importance' => Notification::TYPE_IMPORTANCE_IMMEDIATELY,
            'transports' => [Transport::TRANSPORT_EMAIL],
            'recipients' => [
                ['type' => NotificationRecipients::TYPE_SELF, 'value' => null]
            ],
            'scopes' => [
                [
                    'type' => [ScopeType::USER, ScopeType::SUBTYPE_ANY, ScopeType::GENERAL_SCOPE_CATEGORY],
                    'value' => null
                ],
            ],
            'eventTrackingTimeFrom' => Notification::DEFAULT_EVENT_TRACKING_TIME_FROM,
            'eventTrackingTimeUntil' => Notification::DEFAULT_EVENT_TRACKING_TIME_UNTIL,
            'eventTrackingDays' => Notification::DEFAULT_EVENT_TRACKING_DAYS,
        ],
        [
            'title' => '[' .Event::USER_CREATED. '] User Notification for admin by team admin',
            'status' => Notification::STATUS_ENABLED,
            'event' => [Event::USER_CREATED, Event::TYPE_USER],
            'listenerTeam' => InitTeamsFixture::ADMIN_TEAM['id'],
            'ownerTeam' => InitTeamsFixture::ADMIN_TEAM['id'],
            'importance' => Notification::TYPE_IMPORTANCE_IMMEDIATELY,
            'transports' => Transport::ALLOWED_TRANSPORTS,
            'recipients' => [
                [
                    'type' => NotificationRecipients::TYPE_ROLE,
                    'value' => [[Role::ROLE_SUPER_ADMIN, Team::TEAM_ADMIN]]
                ]
            ],
            'scopes' => [
                [
                    'type' => [ScopeType::USER, ScopeType::SUBTYPE_ANY, ScopeType::GENERAL_SCOPE_CATEGORY],
                    'value' => null
                ],
            ],
            'eventTrackingTimeFrom' => Notification::DEFAULT_EVENT_TRACKING_TIME_FROM,
            'eventTrackingTimeUntil' => Notification::DEFAULT_EVENT_TRACKING_TIME_UNTIL,
            'eventTrackingDays' => Notification::DEFAULT_EVENT_TRACKING_DAYS,
        ],
        [
            'title' => '[' .Event::USER_BLOCKED. '] User Notification for admin by team admin',
            'status' => Notification::STATUS_ENABLED,
            'event' => [Event::USER_BLOCKED, Event::TYPE_USER],
            'listenerTeam' => InitTeamsFixture::ADMIN_TEAM['id'],
            'ownerTeam' => InitTeamsFixture::ADMIN_TEAM['id'],
            'importance' => Notification::TYPE_IMPORTANCE_IMMEDIATELY,
            'transports' => [Transport::TRANSPORT_EMAIL],
            'recipients' => [
                [
                    'type' => NotificationRecipients::TYPE_ROLE,
                    'value' => [[Role::ROLE_SUPER_ADMIN, Team::TEAM_ADMIN]]
                ]
            ],
            'scopes' => [
                [
                    'type' => [ScopeType::USER, ScopeType::SUBTYPE_ANY, ScopeType::GENERAL_SCOPE_CATEGORY],
                    'value' => null
                ],
            ],
            'eventTrackingTimeFrom' => Notification::DEFAULT_EVENT_TRACKING_TIME_FROM,
            'eventTrackingTimeUntil' => Notification::DEFAULT_EVENT_TRACKING_TIME_UNTIL,
            'eventTrackingDays' => Notification::DEFAULT_EVENT_TRACKING_DAYS,
        ],
        [
            'title' => '[' .Event::USER_DELETED. '] User Notification for admin by team admin',
            'status' => Notification::STATUS_ENABLED,
            'event' => [Event::USER_DELETED, Event::TYPE_USER],
            'listenerTeam' => InitTeamsFixture::ADMIN_TEAM['id'],
            'ownerTeam' => InitTeamsFixture::ADMIN_TEAM['id'],
            'importance' => Notification::TYPE_IMPORTANCE_IMMEDIATELY,
            'transports' => [Transport::TRANSPORT_EMAIL],
            'recipients' => [
                [
                    'type' => NotificationRecipients::TYPE_ROLE,
                    'value' => [[Role::ROLE_SUPER_ADMIN, Team::TEAM_ADMIN]]
                ]
            ],
            'scopes' => [
                [
                    'type' => [ScopeType::USER, ScopeType::SUBTYPE_ANY, ScopeType::GENERAL_SCOPE_CATEGORY],
                    'value' => null
                ],
            ],
            'eventTrackingTimeFrom' => Notification::DEFAULT_EVENT_TRACKING_TIME_FROM,
            'eventTrackingTimeUntil' => Notification::DEFAULT_EVENT_TRACKING_TIME_UNTIL,
            'eventTrackingDays' => Notification::DEFAULT_EVENT_TRACKING_DAYS,
        ],
        [
            'title' => '[' .Event::USER_PWD_RESET. '] User Notification for admin by team admin',
            'status' => Notification::STATUS_ENABLED,
            'event' => [Event::USER_PWD_RESET, Event::TYPE_USER],
            'listenerTeam' => InitTeamsFixture::ADMIN_TEAM['id'],
            'ownerTeam' => InitTeamsFixture::ADMIN_TEAM['id'],
            'importance' => Notification::TYPE_IMPORTANCE_IMMEDIATELY,
            'transports' => [Transport::TRANSPORT_EMAIL],
            'recipients' => [
                [
                    'type' => NotificationRecipients::TYPE_ROLE,
                    'value' => [[Role::ROLE_SUPER_ADMIN, Team::TEAM_ADMIN]]
                ]
            ],
            'scopes' => [
                [
                    'type' => [ScopeType::USER, ScopeType::SUBTYPE_ANY, ScopeType::GENERAL_SCOPE_CATEGORY],
                    'value' => null
                ],
            ],
            'eventTrackingTimeFrom' => Notification::DEFAULT_EVENT_TRACKING_TIME_FROM,
            'eventTrackingTimeUntil' => Notification::DEFAULT_EVENT_TRACKING_TIME_UNTIL,
            'eventTrackingDays' => Notification::DEFAULT_EVENT_TRACKING_DAYS,
        ],
        [
            'title' => '[' .Event::CLIENT_CREATED. '] Client Notification for admin by team admin',
            'status' => Notification::STATUS_ENABLED,
            'event' => [Event::CLIENT_CREATED, Event::TYPE_USER],
            'listenerTeam' => InitTeamsFixture::ADMIN_TEAM['id'],
            'ownerTeam' => InitTeamsFixture::ADMIN_TEAM['id'],
            'importance' => Notification::TYPE_IMPORTANCE_IMMEDIATELY,
            'transports' => [Transport::TRANSPORT_EMAIL],
            'recipients' => [
                [
                    'type' => NotificationRecipients::TYPE_ROLE,
                    'value' => [[Role::ROLE_SUPER_ADMIN, Team::TEAM_ADMIN]]
                ]
            ],
            'scopes' => [
                [
                    'type' => [ScopeType::TEAM, ScopeType::SUBTYPE_ANY, ScopeType::GENERAL_SCOPE_CATEGORY],
                    'value' => null
                ],
            ],
            'eventTrackingTimeFrom' => Notification::DEFAULT_EVENT_TRACKING_TIME_FROM,
            'eventTrackingTimeUntil' => Notification::DEFAULT_EVENT_TRACKING_TIME_UNTIL,
            'eventTrackingDays' => Notification::DEFAULT_EVENT_TRACKING_DAYS,
        ],
        [
            'title' => '[' .Event::CLIENT_BLOCKED. '] Client Notification for admin by team admin',
            'status' => Notification::STATUS_ENABLED,
            'event' => [Event::CLIENT_BLOCKED, Event::TYPE_USER],
            'listenerTeam' => InitTeamsFixture::ADMIN_TEAM['id'],
            'ownerTeam' => InitTeamsFixture::ADMIN_TEAM['id'],
            'importance' => Notification::TYPE_IMPORTANCE_IMMEDIATELY,
            'transports' => [Transport::TRANSPORT_EMAIL],
            'recipients' => [
                [
                    'type' => NotificationRecipients::TYPE_ROLE,
                    'value' => [[Role::ROLE_SUPER_ADMIN, Team::TEAM_ADMIN]]
                ]
            ],
            'scopes' => [
                [
                    'type' => [ScopeType::TEAM, ScopeType::SUBTYPE_ANY, ScopeType::GENERAL_SCOPE_CATEGORY],
                    'value' => null
                ],
            ],
            'eventTrackingTimeFrom' => Notification::DEFAULT_EVENT_TRACKING_TIME_FROM,
            'eventTrackingTimeUntil' => Notification::DEFAULT_EVENT_TRACKING_TIME_UNTIL,
            'eventTrackingDays' => Notification::DEFAULT_EVENT_TRACKING_DAYS,
        ],
        [
            'title' => '[' .Event::CLIENT_DEMO_EXPIRED. '] Client Notification for admin by team admin',
            'status' => Notification::STATUS_DISABLED,
            'event' => [Event::CLIENT_DEMO_EXPIRED, Event::TYPE_USER],
            'listenerTeam' => InitTeamsFixture::ADMIN_TEAM['id'],
            'ownerTeam' => InitTeamsFixture::ADMIN_TEAM['id'],
            'importance' => Notification::TYPE_IMPORTANCE_IMMEDIATELY,
            'transports' => [Transport::TRANSPORT_EMAIL],
            'recipients' => [
                [
                    'type' => NotificationRecipients::TYPE_ROLE,
                    'value' => [[Role::ROLE_SUPER_ADMIN, Team::TEAM_ADMIN]]
                ]
            ],
            'scopes' => [
                [
                    'type' => [ScopeType::TEAM, ScopeType::SUBTYPE_ANY, ScopeType::GENERAL_SCOPE_CATEGORY],
                    'value' => null
                ],
            ],
            'eventTrackingTimeFrom' => Notification::DEFAULT_EVENT_TRACKING_TIME_FROM,
            'eventTrackingTimeUntil' => Notification::DEFAULT_EVENT_TRACKING_TIME_UNTIL,
            'eventTrackingDays' => Notification::DEFAULT_EVENT_TRACKING_DAYS,
        ],
        [
            'title' => '[' .Event::VEHICLE_CREATED. '] Vehicle Notification for admin by team admin',
            'status' => Notification::STATUS_ENABLED,
            'event' => [Event::VEHICLE_CREATED, Event::TYPE_USER],
            'listenerTeam' => InitTeamsFixture::ADMIN_TEAM['id'],
            'ownerTeam' => InitTeamsFixture::ADMIN_TEAM['id'],
            'importance' => Notification::TYPE_IMPORTANCE_IMMEDIATELY,
            'transports' => [Transport::TRANSPORT_EMAIL],
            'recipients' => [
                [
                    'type' => NotificationRecipients::TYPE_ROLE,
                    'value' => [[Role::ROLE_SUPER_ADMIN, Team::TEAM_ADMIN]]
                ]
            ],
            'scopes' => [
                [
                    'type' => [ScopeType::VEHICLE, ScopeType::SUBTYPE_ANY, ScopeType::GENERAL_SCOPE_CATEGORY],
                    'value' => null
                ],
            ],
            'eventTrackingTimeFrom' => Notification::DEFAULT_EVENT_TRACKING_TIME_FROM,
            'eventTrackingTimeUntil' => Notification::DEFAULT_EVENT_TRACKING_TIME_UNTIL,
            'eventTrackingDays' => Notification::DEFAULT_EVENT_TRACKING_DAYS,
        ],
        [
            'title' => '[' .Event::VEHICLE_DELETED. '] Vehicle Notification for admin by team admin',
            'status' => Notification::STATUS_ENABLED,
            'event' => [Event::VEHICLE_DELETED, Event::TYPE_USER],
            'listenerTeam' => InitTeamsFixture::ADMIN_TEAM['id'],
            'ownerTeam' => InitTeamsFixture::ADMIN_TEAM['id'],
            'importance' => Notification::TYPE_IMPORTANCE_IMMEDIATELY,
            'transports' => [Transport::TRANSPORT_EMAIL],
            'recipients' => [
                [
                    'type' => NotificationRecipients::TYPE_ROLE,
                    'value' => [[Role::ROLE_SUPER_ADMIN, Team::TEAM_ADMIN]]
                ]
            ],
            'scopes' => [
                [
                    'type' => [ScopeType::VEHICLE, ScopeType::SUBTYPE_ANY, ScopeType::GENERAL_SCOPE_CATEGORY],
                    'value' => null
                ],
            ],
            'eventTrackingTimeFrom' => Notification::DEFAULT_EVENT_TRACKING_TIME_FROM,
            'eventTrackingTimeUntil' => Notification::DEFAULT_EVENT_TRACKING_TIME_UNTIL,
            'eventTrackingDays' => Notification::DEFAULT_EVENT_TRACKING_DAYS,
        ],
        [
            'title' => '[' .Event::VEHICLE_UNAVAILABLE. '] Vehicle Notification for admin by team admin',
            'status' => Notification::STATUS_ENABLED,
            'event' => [Event::VEHICLE_UNAVAILABLE, Event::TYPE_USER],
            'listenerTeam' => InitTeamsFixture::ADMIN_TEAM['id'],
            'ownerTeam' => InitTeamsFixture::ADMIN_TEAM['id'],
            'importance' => Notification::TYPE_IMPORTANCE_IMMEDIATELY,
            'transports' => [Transport::TRANSPORT_EMAIL],
            'recipients' => [
                [
                    'type' => NotificationRecipients::TYPE_ROLE,
                    'value' => [[Role::ROLE_SUPER_ADMIN, Team::TEAM_ADMIN]]
                ]
            ],
            'scopes' => [
                [
                    'type' => [ScopeType::VEHICLE, ScopeType::SUBTYPE_ANY, ScopeType::GENERAL_SCOPE_CATEGORY],
                    'value' => null
                ],
            ],
            'eventTrackingTimeFrom' => Notification::DEFAULT_EVENT_TRACKING_TIME_FROM,
            'eventTrackingTimeUntil' => Notification::DEFAULT_EVENT_TRACKING_TIME_UNTIL,
            'eventTrackingDays' => Notification::DEFAULT_EVENT_TRACKING_DAYS,
        ],
        [
            'title' => '[' .Event::VEHICLE_ONLINE. '] Vehicle Notification for admin by team admin',
            'status' => Notification::STATUS_ENABLED,
            'event' => [Event::VEHICLE_ONLINE, Event::TYPE_USER],
            'listenerTeam' => InitTeamsFixture::ADMIN_TEAM['id'],
            'ownerTeam' => InitTeamsFixture::ADMIN_TEAM['id'],
            'importance' => Notification::TYPE_IMPORTANCE_IMMEDIATELY,
            'transports' => [Transport::TRANSPORT_EMAIL],
            'recipients' => [
                [
                    'type' => NotificationRecipients::TYPE_ROLE,
                    'value' => [[Role::ROLE_SUPER_ADMIN, Team::TEAM_ADMIN]]
                ]
            ],
            'scopes' => [
                [
                    'type' => [ScopeType::VEHICLE, ScopeType::SUBTYPE_ANY, ScopeType::GENERAL_SCOPE_CATEGORY],
                    'value' => null
                ],
            ],
            'eventTrackingTimeFrom' => Notification::DEFAULT_EVENT_TRACKING_TIME_FROM,
            'eventTrackingTimeUntil' => Notification::DEFAULT_EVENT_TRACKING_TIME_UNTIL,
            'eventTrackingDays' => Notification::DEFAULT_EVENT_TRACKING_DAYS,
        ],
        [
            'title' => '[' .Event::VEHICLE_OFFLINE. '] Vehicle Notification for admin by team admin',
            'status' => Notification::STATUS_ENABLED,
            'event' => [Event::VEHICLE_OFFLINE, Event::TYPE_USER],
            'listenerTeam' => InitTeamsFixture::ADMIN_TEAM['id'],
            'ownerTeam' => InitTeamsFixture::ADMIN_TEAM['id'],
            'importance' => Notification::TYPE_IMPORTANCE_IMMEDIATELY,
            'transports' => [Transport::TRANSPORT_EMAIL],
            'recipients' => [
                [
                    'type' => NotificationRecipients::TYPE_ROLE,
                    'value' => [[Role::ROLE_SUPER_ADMIN, Team::TEAM_ADMIN]]
                ]
            ],
            'scopes' => [
                [
                    'type' => [ScopeType::VEHICLE, ScopeType::SUBTYPE_ANY, ScopeType::GENERAL_SCOPE_CATEGORY],
                    'value' => null
                ],
            ],
            'eventTrackingTimeFrom' => Notification::DEFAULT_EVENT_TRACKING_TIME_FROM,
            'eventTrackingTimeUntil' => Notification::DEFAULT_EVENT_TRACKING_TIME_UNTIL,
            'eventTrackingDays' => Notification::DEFAULT_EVENT_TRACKING_DAYS,
        ],
        [
            'title' => '[' .Event::SERVICE_REMINDER_EXPIRED. '] Service Remainder Notification for admin by team admin',
            'status' => Notification::STATUS_DISABLED,
            'event' => [Event::SERVICE_REMINDER_EXPIRED, Event::TYPE_USER],
            'listenerTeam' => InitTeamsFixture::ADMIN_TEAM['id'],
            'ownerTeam' => InitTeamsFixture::ADMIN_TEAM['id'],
            'importance' => Notification::TYPE_IMPORTANCE_IMMEDIATELY,
            'transports' => [Transport::TRANSPORT_EMAIL],
            'recipients' => [
                [
                    'type' => NotificationRecipients::TYPE_ROLE,
                    'value' => [[Role::ROLE_SUPER_ADMIN, Team::TEAM_ADMIN]]
                ]
            ],
            'scopes' => [
                [
                    'type' => [ScopeType::VEHICLE, ScopeType::SUBTYPE_ANY, ScopeType::GENERAL_SCOPE_CATEGORY],
                    'value' => null
                ],
            ],
            'eventTrackingTimeFrom' => Notification::DEFAULT_EVENT_TRACKING_TIME_FROM,
            'eventTrackingTimeUntil' => Notification::DEFAULT_EVENT_TRACKING_TIME_UNTIL,
            'eventTrackingDays' => Notification::DEFAULT_EVENT_TRACKING_DAYS,
        ],
        [
            'title' => '[' .Event::SERVICE_REMINDER_EXPIRED. '] Service Remainder Notification for admin by team admin',
            'status' => Notification::STATUS_DISABLED,
            'event' => [Event::SERVICE_REMINDER_EXPIRED, Event::TYPE_USER],
            'listenerTeam' => InitTeamsFixture::ADMIN_TEAM['id'],
            'ownerTeam' => InitTeamsFixture::ADMIN_TEAM['id'],
            'importance' => Notification::TYPE_IMPORTANCE_IMMEDIATELY,
            'transports' => [Transport::TRANSPORT_EMAIL],
            'recipients' => [
                [
                    'type' => NotificationRecipients::TYPE_ROLE,
                    'value' => [[Role::ROLE_SUPER_ADMIN, Team::TEAM_ADMIN]]
                ]
            ],
            'scopes' => [
                [
                    'type' => [ScopeType::VEHICLE, ScopeType::SUBTYPE_ANY, ScopeType::GENERAL_SCOPE_CATEGORY],
                    'value' => null
                ],
            ],
            'eventTrackingTimeFrom' => Notification::DEFAULT_EVENT_TRACKING_TIME_FROM,
            'eventTrackingTimeUntil' => Notification::DEFAULT_EVENT_TRACKING_TIME_UNTIL,
            'eventTrackingDays' => Notification::DEFAULT_EVENT_TRACKING_DAYS,
        ],
        [
            'title' => '[' .Event::DOCUMENT_EXPIRED. '] Service Remainder Notification for admin by team admin',
            'status' => Notification::STATUS_DISABLED,
            'event' => [Event::DOCUMENT_EXPIRED, Event::TYPE_USER],
            'listenerTeam' => InitTeamsFixture::ADMIN_TEAM['id'],
            'ownerTeam' => InitTeamsFixture::ADMIN_TEAM['id'],
            'importance' => Notification::TYPE_IMPORTANCE_IMMEDIATELY,
            'transports' => [Transport::TRANSPORT_WEB_APP],
            'recipients' => [
                [
                    'type' => NotificationRecipients::TYPE_ROLE,
                    'value' => [[Role::ROLE_SUPER_ADMIN, Team::TEAM_ADMIN]]
                ]
            ],
            'scopes' => [
                [
                    'type' => [ScopeType::VEHICLE, ScopeType::SUBTYPE_ANY, ScopeType::GENERAL_SCOPE_CATEGORY],
                    'value' => null
                ],
            ],
            'eventTrackingTimeFrom' => Notification::DEFAULT_EVENT_TRACKING_TIME_FROM,
            'eventTrackingTimeUntil' => Notification::DEFAULT_EVENT_TRACKING_TIME_UNTIL,
            'eventTrackingDays' => Notification::DEFAULT_EVENT_TRACKING_DAYS,
        ],
        [
            'title' => '[' .Event::DOCUMENT_EXPIRE_SOON. '] Service Remainder Notification for admin by team admin',
            'status' => Notification::STATUS_DISABLED,
            'event' => [Event::DOCUMENT_EXPIRE_SOON, Event::TYPE_USER],
            'listenerTeam' => InitTeamsFixture::ADMIN_TEAM['id'],
            'ownerTeam' => InitTeamsFixture::ADMIN_TEAM['id'],
            'importance' => Notification::TYPE_IMPORTANCE_IMMEDIATELY,
            'transports' => [Transport::TRANSPORT_WEB_APP],
            'recipients' => [
                [
                    'type' => NotificationRecipients::TYPE_ROLE,
                    'value' => [[Role::ROLE_SUPER_ADMIN, Team::TEAM_ADMIN]]
                ]
            ],
            'scopes' => [
                [
                    'type' => [ScopeType::VEHICLE, ScopeType::SUBTYPE_ANY, ScopeType::GENERAL_SCOPE_CATEGORY],
                    'value' => null
                ],
            ],
            'eventTrackingTimeFrom' => Notification::DEFAULT_EVENT_TRACKING_TIME_FROM,
            'eventTrackingTimeUntil' => Notification::DEFAULT_EVENT_TRACKING_TIME_UNTIL,
            'eventTrackingDays' => Notification::DEFAULT_EVENT_TRACKING_DAYS,
        ],
        [
            'title' => 'Digital form is not completed - for driver',
            'status' => Notification::STATUS_ENABLED,
            'event' => [Event::DIGITAL_FORM_IS_NOT_COMPLETED, Event::TYPE_SYSTEM],
            'listenerTeam' => null,
            'ownerTeam' => null,
            'importance' => Notification::TYPE_IMPORTANCE_IMMEDIATELY,
            'transports' => [Transport::TRANSPORT_WEB_APP, Transport::TRANSPORT_MOBILE_APP],
            'recipients' => [
                ['type' => NotificationRecipients::TYPE_SELF, 'value' => null]
            ],
            'scopes' => [
                [
                    'type' => [ScopeType::VEHICLE, ScopeType::SUBTYPE_ANY, ScopeType::GENERAL_SCOPE_CATEGORY],
                    'value' => null
                ],
            ],
            'eventTrackingTimeFrom' => Notification::DEFAULT_EVENT_TRACKING_TIME_FROM,
            'eventTrackingTimeUntil' => Notification::DEFAULT_EVENT_TRACKING_TIME_UNTIL,
            'eventTrackingDays' => Notification::ALL_EVENT_TRACKING_DAYS,
        ],
    ];

    public function getDependencies(): array
    {
        return [
            InitEventsFixture::class,
            InitTransportsFixture::class,
            InitTeamsFixture::class,
            InitScopeTypesFixture::class,
        ];
    }

    /**
     * @return array
     */
    public static function getGroups(): array
    {
        return [FixturesTypes::GLOBAL];
    }

    public function load(ObjectManager $manager): void
    {
        /** @var EntityManager $manager */
        $manager = $this->prepareEntityManager($manager);

        foreach (self::DATA as $notificationData) {
            /** @var Notification $notificationObj */
            $notificationObj = $manager->getRepository(Notification::class)->findOneBy(
                [
                    'event' => $this->getReference(\implode('_', $notificationData['event']))
                ]
            );

            if (!$notificationObj) {
                $notification = new Notification([]);
                $notification
                    ->setTitle($notificationData['title'])
                    ->setStatus($notificationData['status'])
                    ->setEvent($this->getReference(\implode('_', $notificationData['event'])))
                    ->setEventTrackingDays($notificationData['eventTrackingDays'])
                    ->setImportance($notificationData['importance']);

                if (!empty($notificationData['listenerTeam'])) {
                    $notification->setListenerTeam($this->getReference('TEAM_' . $notificationData['listenerTeam']));
                }

                if (!empty($notificationData['ownerTeam'])) {
                    $notification->setOwnerTeam($this->getReference('TEAM_' . $notificationData['ownerTeam']));
                }

                $manager->persist($notification);

                foreach ($notificationData['transports'] as $transportAlias) {
                    $nTransports = new NotificationTransports();
                    $nTransports->setNotification($notification);
                    $nTransports->setTransport($this->getReference($transportAlias));

                    $manager->persist($nTransports);
                }

                foreach ($notificationData['recipients'] as $recipientData) {
                    $recipient = new NotificationRecipients();
                    $recipient->setType($recipientData['type']);
                    $recipient->setNotification($notification);

                    switch ($recipientData['type']) {
                        case NotificationRecipients::TYPE_ROLE:
                            $recipient->setValue(
                                array_map(
                                    function ($v) {
                                        return $this->getReference(implode('_', $v))->getId();
                                    },
                                    $recipientData['value']
                                )
                            );
                            break;
                        case NotificationRecipients::TYPE_USERS_LIST:
                            $recipient->setValue(
                                array_map(
                                    static function ($v) use ($manager) {
                                        return $manager->getRepository(User::class)
                                            ->findOneBy(['email' => $v])
                                            ->getId();
                                    },
                                    $recipientData['value']
                                )
                            );
                            break;
                    }
                    $manager->persist($recipient);
                }

                foreach ($notificationData['scopes'] as $scope) {
                    $notificationScope = new NotificationScopes();
                    $notificationScope->setNotification($notification);
                    $notificationScope->setType($this->getReference(implode('_', $scope['type'])));
                    $notificationScope->setValue($scope['value']);

                    $manager->persist($notificationScope);
                }
            } else {
                $notificationObj
                    ->setEventTrackingTimeFrom($notificationData['eventTrackingTimeFrom'])
                    ->setEventTrackingTimeUntil($notificationData['eventTrackingTimeUntil'])
                    ->setEventTrackingDays($notificationData['eventTrackingDays']);
            }
        }

        $manager->flush();
    }
}
