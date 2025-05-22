<?php

namespace App\Fixtures\Notification;

use App\Entity\EventLog\EventLog;
use App\Entity\Importance;
use App\Entity\Notification\Event;
use App\Entity\Notification\ScopeType;
use App\Fixtures\BaseFixture;
use App\Fixtures\FixturesTypes;
use App\Fixtures\Importance\IntImportanceFixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Doctrine\ORM\EntityManager;

class InitEventsFixture extends BaseFixture implements DependentFixtureInterface, FixtureGroupInterface
{
    public const DATA = [
        [
            'implemented' => true,
            'name' => Event::VEHICLE_CREATED,
            'alias' => 'Vehicle added',
            'type' => Event::TYPE_USER,
            'entity' => Event::ENTITY_TYPE_VEHICLE,
            'scopes' => [
                ScopeType::VEHICLE =>
                    [
                        ScopeType::GENERAL_SCOPE_CATEGORY =>
                            ScopeType::SCOPE_TO_SUBTYPES[ScopeType::GENERAL_SCOPE_CATEGORY][ScopeType::VEHICLE],
                    ]
            ],
            'importance' => Importance::TYPE_NORMAL,
            'triggeredBy' => Event::BY_USER,
            Event::ADDITIONAL_SETTING => [
                Event::ADDITIONAL_SETTING_HEADER_COLUMNS => [
                    EventLog::EVENT_LOG_ID => 'headers.general.id',
                    EventLog::DATE => 'headers.general.formattedDate',
                    EventLog::IMPORTANCE => 'headers.general.importance',
                    EventLog::TRIGGERED_DETAILS => 'headers.vehicleInfo.triggeredDetails',
                    EventLog::EVENT_TEAM => 'headers.general.eventTeam',
                    EventLog::EVENT_SOURCE => 'headers.vehicleInfo.eventSource',
                    EventLog::NTF_LIST => 'headers.general.notificationsList',
                ],
                Event::SETTING_EVENT_LOG => [
                    Event::ADDITIONAL_SETTING_MAPPING_FILTERS => [
                        EventLog::EVENT_ID => 'eventId',
                        EventLog::EVENT_LOG_ID => 'id',
                        EventLog::DATE => 'eventDate',
                        EventLog::TRIGGERED_DETAILS => 'userBy',
                        EventLog::EVENT_TEAM => 'entityTeamId',
                    ],
                ],
            ],
        ],
        [
            'implemented' => true,
            'name' => Event::VEHICLE_DELETED,
            'alias' => 'Vehicle deleted',
            'type' => Event::TYPE_USER,
            'entity' => Event::ENTITY_TYPE_VEHICLE,
            'scopes' => [
                ScopeType::VEHICLE =>
                    [
                        ScopeType::GENERAL_SCOPE_CATEGORY =>
                            ScopeType::SCOPE_TO_SUBTYPES[ScopeType::GENERAL_SCOPE_CATEGORY][ScopeType::VEHICLE],
                    ]
            ],
            'importance' => Importance::TYPE_NORMAL,
            'triggeredBy' => Event::BY_USER,
            Event::ADDITIONAL_SETTING => [
                Event::ADDITIONAL_SETTING_HEADER_COLUMNS => [
                    EventLog::EVENT_LOG_ID => 'headers.general.id',
                    EventLog::DATE => 'headers.general.formattedDate',
                    EventLog::IMPORTANCE => 'headers.general.importance',
                    EventLog::TRIGGERED_DETAILS => 'headers.vehicleInfo.triggeredDetails',
                    EventLog::EVENT_TEAM => 'headers.general.eventTeam',
                    EventLog::EVENT_SOURCE => 'headers.vehicleInfo.eventSource',
                    EventLog::NTF_LIST => 'headers.general.notificationsList',
                ],
                Event::SETTING_EVENT_LOG => [
                    Event::ADDITIONAL_SETTING_MAPPING_FILTERS => [
                        EventLog::EVENT_ID => 'eventId',
                        EventLog::EVENT_LOG_ID => 'id',
                        EventLog::DATE => 'eventDate',
                        EventLog::TRIGGERED_DETAILS => 'userBy',
                        EventLog::EVENT_TEAM => 'entityTeamId',
                    ],
                ],
            ],
        ],
        [
            'implemented' => true,
            'name' => Event::VEHICLE_UNAVAILABLE,
            'alias' => 'Vehicle unavailable',
            'type' => Event::TYPE_USER,
            'entity' => Event::ENTITY_TYPE_VEHICLE,
            'scopes' => [
                ScopeType::VEHICLE =>
                    [
                        ScopeType::GENERAL_SCOPE_CATEGORY =>
                            ScopeType::SCOPE_TO_SUBTYPES[ScopeType::GENERAL_SCOPE_CATEGORY][ScopeType::VEHICLE],
                    ]
            ],
            'importance' => Importance::TYPE_NORMAL,
            'triggeredBy' => Event::BY_USER,
            Event::ADDITIONAL_SETTING => [
                Event::ADDITIONAL_SETTING_HEADER_COLUMNS => [
                    EventLog::EVENT_LOG_ID => 'headers.general.id',
                    EventLog::DATE => 'headers.general.formattedDate',
                    EventLog::IMPORTANCE => 'headers.general.importance',
                    EventLog::TRIGGERED_DETAILS => 'headers.vehicleInfo.triggeredDetails',
                    EventLog::EVENT_TEAM => 'headers.general.eventTeam',
                    EventLog::EVENT_SOURCE => 'headers.vehicleInfo.eventSource',
                    EventLog::NTF_LIST => 'headers.general.notificationsList',
                ],
                Event::SETTING_EVENT_LOG => [
                    Event::ADDITIONAL_SETTING_MAPPING_FILTERS => [
                        EventLog::EVENT_ID => 'eventId',
                        EventLog::EVENT_LOG_ID => 'id',
                        EventLog::DATE => 'eventDate',
                        EventLog::TRIGGERED_DETAILS => 'userBy',
                        EventLog::EVENT_TEAM => 'entityTeamId',
                    ],
                ],
            ],
        ],
        [
            'implemented' => true,
            'name' => Event::VEHICLE_OFFLINE,
            'alias' => 'Vehicle offline',
            'type' => Event::TYPE_USER,
            'entity' => Event::ENTITY_TYPE_VEHICLE,
            'scopes' => [
                ScopeType::VEHICLE =>
                    [
                        ScopeType::GENERAL_SCOPE_CATEGORY =>
                            ScopeType::SCOPE_TO_SUBTYPES[ScopeType::GENERAL_SCOPE_CATEGORY][ScopeType::VEHICLE],
                    ]
            ],
            'importance' => Importance::TYPE_NORMAL,
            Event::ADDITIONAL_SETTING => [
                Event::ADDITIONAL_SETTING_HEADER_COLUMNS => [
                    EventLog::EVENT_LOG_ID => 'headers.general.id',
                    EventLog::DATE => 'headers.general.formattedDate',
                    EventLog::IMPORTANCE => 'headers.general.importance',
                    EventLog::EVENT_TEAM => 'headers.general.eventTeam',
                    EventLog::EVENT_SOURCE => 'headers.vehicleInfo.eventSource',
                    EventLog::DURATION => 'headers.trackerInfo.durationOffline',
                    EventLog::SHORT_DETAILS => 'headers.trackerInfo.shortDetails',
                    EventLog::NTF_LIST => 'headers.general.notificationsList',
                ],
                Event::SETTING_EVENT_LOG => [
                    Event::ADDITIONAL_SETTING_MAPPING_FILTERS => [
                        EventLog::EVENT_ID => 'eventId',
                        EventLog::EVENT_LOG_ID => 'id',
                        EventLog::DATE => 'eventDate',
                        EventLog::EVENT_TEAM => 'entityTeamId',
                    ],
                ],
            ],
        ],
        [
            'implemented' => true,
            'name' => Event::VEHICLE_ONLINE,
            'alias' => 'Vehicle online',
            'type' => Event::TYPE_USER,
            'entity' => Event::ENTITY_TYPE_VEHICLE,
            'scopes' => [
                ScopeType::VEHICLE =>
                    [
                        ScopeType::GENERAL_SCOPE_CATEGORY =>
                            ScopeType::SCOPE_TO_SUBTYPES[ScopeType::GENERAL_SCOPE_CATEGORY][ScopeType::VEHICLE],
                    ]
            ],
            'importance' => Importance::TYPE_NORMAL,
            Event::ADDITIONAL_SETTING => [
                Event::ADDITIONAL_SETTING_HEADER_COLUMNS => [
                    EventLog::EVENT_LOG_ID => 'headers.general.id',
                    EventLog::DATE => 'headers.general.formattedDate',
                    EventLog::IMPORTANCE => 'headers.general.importance',
                    EventLog::EVENT_TEAM => 'headers.general.eventTeam',
                    EventLog::EVENT_SOURCE => 'headers.vehicleInfo.eventSource',
                    EventLog::NTF_LIST => 'headers.general.notificationsList',
                ],
                Event::SETTING_EVENT_LOG => [
                    Event::ADDITIONAL_SETTING_MAPPING_FILTERS => [
                        EventLog::EVENT_ID => 'eventId',
                        EventLog::EVENT_LOG_ID => 'id',
                        EventLog::DATE => 'eventDate',
                        EventLog::EVENT_TEAM => 'entityTeamId',
                    ],
                ],
            ],
        ],
        [
            'implemented' => true,
            'name' => Event::VEHICLE_CHANGED_REGNO,
            'alias' => 'Vehicle rego number changed',
            'type' => Event::TYPE_USER,
            'entity' => Event::ENTITY_TYPE_VEHICLE,
            'scopes' => [
                ScopeType::VEHICLE =>
                    [
                        ScopeType::GENERAL_SCOPE_CATEGORY =>
                            ScopeType::SCOPE_TO_SUBTYPES[ScopeType::GENERAL_SCOPE_CATEGORY][ScopeType::VEHICLE],
                    ]
            ],
            'importance' => Importance::TYPE_NORMAL,
            'triggeredBy' => Event::BY_USER,
            Event::ADDITIONAL_SETTING => [
                Event::ADDITIONAL_SETTING_HEADER_COLUMNS => [
                    EventLog::EVENT_LOG_ID => 'headers.general.id',
                    EventLog::DATE => 'headers.general.formattedDate',
                    EventLog::IMPORTANCE => 'headers.general.importance',
                    EventLog::TRIGGERED_DETAILS => 'headers.vehicleInfo.triggeredDetails',
                    EventLog::EVENT_TEAM => 'headers.general.eventTeam',
                    EventLog::OLD_VALUE => 'headers.general.oldValue',
                    EventLog::EVENT_SOURCE => 'headers.vehicleInfo.eventSource',
                    EventLog::NTF_LIST => 'headers.general.notificationsList',
                ],
                Event::SETTING_EVENT_LOG => [
                    Event::ADDITIONAL_SETTING_MAPPING_FILTERS => [
                        EventLog::EVENT_ID => 'eventId',
                        EventLog::EVENT_LOG_ID => 'id',
                        EventLog::DATE => 'eventDate',
                        EventLog::TRIGGERED_DETAILS => 'userBy',
                        EventLog::EVENT_TEAM => 'entityTeamId',
                    ],
                ],
            ],
        ],
        [
            'implemented' => true,
            'name' => Event::VEHICLE_CHANGED_MODEL,
            'alias' => 'Vehicle make/model change',
            'type' => Event::TYPE_USER,
            'entity' => Event::ENTITY_TYPE_VEHICLE,
            'scopes' => [
                ScopeType::VEHICLE =>
                    [
                        ScopeType::GENERAL_SCOPE_CATEGORY =>
                            ScopeType::SCOPE_TO_SUBTYPES[ScopeType::GENERAL_SCOPE_CATEGORY][ScopeType::VEHICLE],
                    ]
            ],
            'importance' => Importance::TYPE_NORMAL,
            'triggeredBy' => Event::BY_USER,
            Event::ADDITIONAL_SETTING => [
                Event::ADDITIONAL_SETTING_HEADER_COLUMNS => [
                    EventLog::EVENT_LOG_ID => 'headers.general.id',
                    EventLog::DATE => 'headers.general.formattedDate',
                    EventLog::IMPORTANCE => 'headers.general.importance',
                    EventLog::TRIGGERED_DETAILS => 'headers.vehicleInfo.triggeredDetails',
                    EventLog::EVENT_TEAM => 'headers.general.eventTeam',
                    EventLog::OLD_VALUE => 'headers.general.oldValue',
                    EventLog::EVENT_SOURCE => 'headers.vehicleInfo.eventSource',
                    EventLog::NTF_LIST => 'headers.general.notificationsList',
                ],
                Event::SETTING_EVENT_LOG => [
                    Event::ADDITIONAL_SETTING_MAPPING_FILTERS => [
                        EventLog::EVENT_ID => 'eventId',
                        EventLog::EVENT_LOG_ID => 'id',
                        EventLog::DATE => 'eventDate',
                        EventLog::TRIGGERED_DETAILS => 'userBy',
                        EventLog::EVENT_TEAM => 'entityTeamId',
                    ],
                ],
            ],
        ],
        [
            'implemented' => true,
            'name' => Event::VEHICLE_REASSIGNED,
            'alias' => 'New driver assigned to vehicle',
            'type' => Event::TYPE_USER,
            'entity' => Event::ENTITY_TYPE_VEHICLE,
            'scopes' => [
                ScopeType::VEHICLE =>
                    [
                        ScopeType::GENERAL_SCOPE_CATEGORY =>
                            ScopeType::SCOPE_TO_SUBTYPES[ScopeType::GENERAL_SCOPE_CATEGORY][ScopeType::VEHICLE],
                    ]
            ],
            'importance' => Importance::TYPE_NORMAL,
            Event::ADDITIONAL_SETTING => [
                Event::ADDITIONAL_SETTING_HEADER_COLUMNS => [
                    EventLog::EVENT_LOG_ID => 'headers.general.id',
                    EventLog::DATE => 'headers.general.formattedDate',
                    EventLog::IMPORTANCE => 'headers.general.importance',
                    EventLog::EVENT_TEAM => 'headers.general.eventTeam',
                    EventLog::EVENT_SOURCE => 'headers.trackerInfo.eventSource',
                    EventLog::OLD_VALUE => 'headers.general.oldValue',
                    EventLog::NEW_VALUE => 'headers.general.newValue',
                    EventLog::NTF_LIST => 'headers.general.notificationsList',
                ],
                Event::SETTING_EVENT_LOG => [
                    Event::ADDITIONAL_SETTING_MAPPING_FILTERS => [
                        EventLog::EVENT_ID => 'eventId',
                        EventLog::EVENT_LOG_ID => 'id',
                        EventLog::DATE => 'eventDate',
                        EventLog::EVENT_TEAM => 'entityTeamId',
                    ],
                ],
            ],
        ],
        [
            'implemented' => true,
            'name' => Event::SERVICE_REMINDER_SOON,
            'alias' => 'Service due soon',
            'type' => Event::TYPE_USER,
            'entity' => Event::ENTITY_TYPE_REMINDER,
            'scopes' => [
                ScopeType::REMINDER =>
                    [
                        ScopeType::GENERAL_SCOPE_CATEGORY =>
                            ScopeType::SCOPE_TO_SUBTYPES[ScopeType::GENERAL_SCOPE_CATEGORY][ScopeType::REMINDER],
                    ]
            ],
            'importance' => Importance::TYPE_NORMAL,
            Event::ADDITIONAL_SETTING => [
                Event::ADDITIONAL_SETTING_HEADER_COLUMNS => [
                    EventLog::EVENT_LOG_ID => 'headers.general.id',
                    EventLog::DATE => 'headers.general.formattedDate',
                    EventLog::IMPORTANCE => 'headers.general.importance',
                    EventLog::EVENT_TEAM => 'headers.general.eventTeam',
                    EventLog::EVENT_SOURCE => 'headers.documentInfo.eventSource',
                    EventLog::VEHICLE_REG_NO => 'headers.documentInfo.vehicleRegNo',
                    EventLog::EXPIRED_DATE => 'headers.documentInfo.expiredDate',
                    EventLog::NTF_LIST => 'headers.general.notificationsList',
                ],
                Event::SETTING_EVENT_LOG => [
                    Event::ADDITIONAL_SETTING_MAPPING_FILTERS => [
                        EventLog::EVENT_ID => 'eventId',
                        EventLog::EVENT_LOG_ID => 'id',
                        EventLog::DATE => 'eventDate',
                        EventLog::EVENT_TEAM => 'entityTeamId',
                    ],
                ],
            ],
        ],
        [
            'implemented' => true,
            'name' => Event::SERVICE_REMINDER_EXPIRED,
            'alias' => 'Service overdue',
            'type' => Event::TYPE_USER,
            'entity' => Event::ENTITY_TYPE_REMINDER,
            'scopes' => [
                ScopeType::REMINDER =>
                    [
                        ScopeType::GENERAL_SCOPE_CATEGORY =>
                            ScopeType::SCOPE_TO_SUBTYPES[ScopeType::GENERAL_SCOPE_CATEGORY][ScopeType::REMINDER],
                    ]
            ],
            'importance' => Importance::TYPE_NORMAL,
            Event::ADDITIONAL_SETTING => [
                Event::ADDITIONAL_SETTING_HEADER_COLUMNS => [
                    EventLog::EVENT_LOG_ID => 'headers.general.id',
                    EventLog::DATE => 'headers.general.formattedDate',
                    EventLog::IMPORTANCE => 'headers.general.importance',
                    EventLog::EVENT_TEAM => 'headers.general.eventTeam',
                    EventLog::EVENT_SOURCE => 'headers.documentInfo.eventSource',
                    EventLog::VEHICLE_REG_NO => 'headers.documentInfo.vehicleRegNo',
                    EventLog::NTF_LIST => 'headers.general.notificationsList',
                ],
                Event::SETTING_EVENT_LOG => [
                    Event::ADDITIONAL_SETTING_MAPPING_FILTERS => [
                        EventLog::EVENT_ID => 'eventId',
                        EventLog::EVENT_LOG_ID => 'id',
                        EventLog::DATE => 'eventDate',
                        EventLog::EVENT_TEAM => 'entityTeamId',
                    ],
                ],
            ],
        ],
        [
            'implemented' => true,
            'name' => Event::SERVICE_REMINDER_DONE,
            'alias' => 'Service completed',
            'type' => Event::TYPE_USER,
            'entity' => Event::ENTITY_TYPE_REMINDER,
            'scopes' => [
                ScopeType::REMINDER =>
                    [
                        ScopeType::GENERAL_SCOPE_CATEGORY =>
                            ScopeType::SCOPE_TO_SUBTYPES[ScopeType::GENERAL_SCOPE_CATEGORY][ScopeType::REMINDER],
                    ]
            ],
            'importance' => Importance::TYPE_NORMAL,
            'triggeredBy' => Event::BY_USER,
            Event::ADDITIONAL_SETTING => [
                Event::ADDITIONAL_SETTING_HEADER_COLUMNS => [
                    EventLog::EVENT_LOG_ID => 'headers.general.id',
                    EventLog::DATE => 'headers.general.formattedDate',
                    EventLog::IMPORTANCE => 'headers.general.importance',
                    EventLog::TRIGGERED_DETAILS => 'headers.documentInfo.triggeredDetails',
                    EventLog::EVENT_TEAM => 'headers.general.eventTeam',
                    EventLog::EVENT_SOURCE => 'headers.documentInfo.eventSource',
                    EventLog::VEHICLE_REG_NO => 'headers.documentInfo.vehicleRegNo',
                    EventLog::NTF_LIST => 'headers.general.notificationsList',
                ],
                Event::SETTING_EVENT_LOG => [
                    Event::ADDITIONAL_SETTING_MAPPING_FILTERS => [
                        EventLog::EVENT_ID => 'eventId',
                        EventLog::EVENT_LOG_ID => 'id',
                        EventLog::DATE => 'eventDate',
                        EventLog::TRIGGERED_DETAILS => 'userBy',
                        EventLog::EVENT_TEAM => 'entityTeamId',
                    ],
                ],
            ],
        ],
        [
            'implemented' => true,
            'name' => Event::SERVICE_REMINDER_DELETED,
            'alias' => 'Service reminder deleted',
            'type' => Event::TYPE_USER,
            'entity' => Event::ENTITY_TYPE_REMINDER,
            'scopes' => [
                ScopeType::REMINDER =>
                    [
                        ScopeType::GENERAL_SCOPE_CATEGORY =>
                            ScopeType::SCOPE_TO_SUBTYPES[ScopeType::GENERAL_SCOPE_CATEGORY][ScopeType::REMINDER],
                    ]
            ],
            'importance' => Importance::TYPE_NORMAL,
            'triggeredBy' => Event::BY_USER,
            Event::ADDITIONAL_SETTING => [
                Event::ADDITIONAL_SETTING_HEADER_COLUMNS => [
                    EventLog::EVENT_LOG_ID => 'headers.general.id',
                    EventLog::DATE => 'headers.general.formattedDate',
                    EventLog::IMPORTANCE => 'headers.general.importance',
                    EventLog::TRIGGERED_DETAILS => 'headers.documentInfo.triggeredDetails',
                    EventLog::EVENT_TEAM => 'headers.general.eventTeam',
                    EventLog::EVENT_SOURCE => 'headers.documentInfo.eventSource',
                    EventLog::VEHICLE_REG_NO => 'headers.documentInfo.vehicleRegNo',
                    EventLog::NTF_LIST => 'headers.general.notificationsList',
                ],
                Event::SETTING_EVENT_LOG => [
                    Event::ADDITIONAL_SETTING_MAPPING_FILTERS => [
                        EventLog::EVENT_ID => 'eventId',
                        EventLog::EVENT_LOG_ID => 'id',
                        EventLog::DATE => 'eventDate',
                        EventLog::TRIGGERED_DETAILS => 'userBy',
                        EventLog::EVENT_TEAM => 'entityTeamId',
                    ],
                ],
            ],
        ],
        [
            'implemented' => true,
            'name' => Event::DOCUMENT_EXPIRE_SOON,
            'alias' => 'Document expiring soon',
            'type' => Event::TYPE_USER,
            'entity' => Event::ENTITY_TYPE_DOCUMENT_RECORD,
            'scopes' => [
                ScopeType::DOCUMENT_RECORD =>
                    [
                        ScopeType::GENERAL_SCOPE_CATEGORY =>
                            ScopeType::SCOPE_TO_SUBTYPES[ScopeType::GENERAL_SCOPE_CATEGORY][ScopeType::DOCUMENT_RECORD],
                    ]
            ],
            Event::ADDITIONAL_SETTING => [
                Event::ADDITIONAL_SETTING_HEADER_COLUMNS => [
                    EventLog::EVENT_LOG_ID => 'headers.general.id',
                    EventLog::DATE => 'headers.general.formattedDate',
                    EventLog::IMPORTANCE => 'headers.general.importance',
                    EventLog::EVENT_SOURCE => 'headers.documentRecordInfo.eventSource',
                    EventLog::TITLE => 'headers.documentRecordInfo.title',
                    EventLog::EXPIRED_DATE => 'headers.documentRecordInfo.expiredDate',
                    EventLog::NTF_LIST => 'headers.general.notificationsList',
                ],
                Event::SETTING_EVENT_LOG => [
                    Event::ADDITIONAL_SETTING_MAPPING_FILTERS => [
                        EventLog::EVENT_LOG_ID => 'id',
                        EventLog::DATE => 'eventDate',
                        EventLog::EVENT_SOURCE => 'vehicleId',
                        EventLog::EVENT_ID => 'eventId',
                        EventLog::EVENT_TEAM => 'entityTeamId'
                    ],
                ],
            ],
            'importance' => Importance::TYPE_NORMAL,
        ],
        [
            'implemented' => true,
            'name' => Event::DOCUMENT_EXPIRED,
            'alias' => 'Document expired',
            'type' => Event::TYPE_USER,
            'entity' => Event::ENTITY_TYPE_DOCUMENT_RECORD,
            'scopes' => [
                ScopeType::DOCUMENT_RECORD =>
                    [
                        ScopeType::GENERAL_SCOPE_CATEGORY =>
                            ScopeType::SCOPE_TO_SUBTYPES[ScopeType::GENERAL_SCOPE_CATEGORY][ScopeType::DOCUMENT_RECORD],
                    ]
            ],
            Event::ADDITIONAL_SETTING => [
                Event::ADDITIONAL_SETTING_HEADER_COLUMNS => [
                    EventLog::EVENT_LOG_ID => 'headers.general.id',
                    EventLog::DATE => 'headers.general.formattedDate',
                    EventLog::IMPORTANCE => 'headers.general.importance',
                    EventLog::EVENT_SOURCE => 'headers.documentInfo.vehicleRegNo',
                    EventLog::TITLE => 'headers.documentRecordInfo.title',
                    EventLog::NTF_LIST => 'headers.general.notificationsList',
                ],
                Event::SETTING_EVENT_LOG => [
                    Event::ADDITIONAL_SETTING_MAPPING_FILTERS => [
                        EventLog::EVENT_LOG_ID => 'id',
                        EventLog::DATE => 'eventDate',
                        EventLog::EVENT_SOURCE => 'vehicleId',
                        EventLog::EVENT_ID => 'eventId',
                        EventLog::EVENT_TEAM => 'entityTeamId'
                    ],
                ],
            ],
            'importance' => Importance::TYPE_NORMAL,
        ],
        [
            'implemented' => true,
            'name' => Event::DOCUMENT_DELETED,
            'alias' => 'Document deleted',
            'type' => Event::TYPE_USER,
            'entity' => Event::ENTITY_TYPE_DOCUMENT,
            'scopes' => [
                ScopeType::DOCUMENT =>
                    [
                        ScopeType::GENERAL_SCOPE_CATEGORY =>
                            ScopeType::SCOPE_TO_SUBTYPES[ScopeType::GENERAL_SCOPE_CATEGORY][ScopeType::DOCUMENT],
                    ]
            ],
            'importance' => Importance::TYPE_NORMAL,
            'triggeredBy' => Event::BY_USER,
            Event::ADDITIONAL_SETTING => [
                Event::ADDITIONAL_SETTING_HEADER_COLUMNS => [
                    EventLog::EVENT_LOG_ID => 'headers.general.id',
                    EventLog::DATE => 'headers.general.formattedDate',
                    EventLog::IMPORTANCE => 'headers.general.importance',
                    EventLog::TRIGGERED_DETAILS => 'headers.documentInfo.triggeredDetails',
                    EventLog::EVENT_SOURCE => 'headers.documentInfo.eventSource',
                    EventLog::NTF_LIST => 'headers.general.notificationsList',
                ],
                Event::SETTING_EVENT_LOG => [
                    Event::ADDITIONAL_SETTING_MAPPING_FILTERS => [
                        EventLog::EVENT_LOG_ID => 'id',
                        EventLog::DATE => 'eventDate',
                        EventLog::TRIGGERED_DETAILS => 'userBy',
                        EventLog::EVENT_TEAM => 'entityTeamId',
//                        EventLog::EVENT_SOURCE => 'entityId',
                        EventLog::EVENT_ID => 'eventId',
                    ],
                ],
            ],
        ],
        [
            'implemented' => true,
            'name' => Event::USER_CREATED,
            'alias' => 'User created',
            'type' => Event::TYPE_USER,
            'entity' => Event::ENTITY_TYPE_USER,
            'scopes' => [
                ScopeType::USER =>
                    [
                        ScopeType::GENERAL_SCOPE_CATEGORY =>
                            ScopeType::SCOPE_TO_SUBTYPES[ScopeType::GENERAL_SCOPE_CATEGORY][ScopeType::USER],
                    ]
            ],
            'importance' => Importance::TYPE_NORMAL,
            'triggeredBy' => Event::BY_USER,
            Event::ADDITIONAL_SETTING => [
                Event::ADDITIONAL_SETTING_HEADER_COLUMNS => [
                    EventLog::EVENT_LOG_ID => 'headers.general.id',
                    EventLog::DATE => 'headers.general.formattedDate',
                    EventLog::IMPORTANCE => 'headers.general.importance',
                    EventLog::TRIGGERED_DETAILS => 'headers.userCreated.triggeredDetails',
                    EventLog::EVENT_TEAM => 'headers.general.eventTeam',
                    EventLog::EVENT_SOURCE => 'headers.userCreated.eventSource',
                    EventLog::NTF_LIST => 'headers.general.notificationsList',
                ],
                Event::SETTING_EVENT_LOG => [
                    Event::ADDITIONAL_SETTING_MAPPING_FILTERS => [
                        EventLog::EVENT_LOG_ID => 'id',
                        EventLog::DATE => 'eventDate',
                        EventLog::TRIGGERED_DETAILS => 'userBy',
                        EventLog::EVENT_TEAM => 'entityTeamId',
                        EventLog::EVENT_ID => 'eventId',
                    ],
                ],
            ],
        ],
        [
            'implemented' => true,
            'name' => Event::USER_CREATED_SYSTEM,
            'alias' => 'User created (system)',
            'type' => Event::TYPE_SYSTEM,
            'entity' => Event::ENTITY_TYPE_USER,
            'scopes' => [
                ScopeType::USER =>
                    [
                        ScopeType::GENERAL_SCOPE_CATEGORY =>
                            ScopeType::SCOPE_TO_SUBTYPES[ScopeType::GENERAL_SCOPE_CATEGORY][ScopeType::USER],
                    ]
            ],
            'importance' => Importance::TYPE_NORMAL,
            'triggeredBy' => Event::BY_USER
        ],
        [
            'implemented' => true,
            'name' => Event::USER_BLOCKED,
            'alias' => 'User blocked',
            'type' => Event::TYPE_USER,
            'entity' => Event::ENTITY_TYPE_USER,
            'scopes' => [
                ScopeType::USER =>
                    [
                        ScopeType::GENERAL_SCOPE_CATEGORY =>
                            ScopeType::SCOPE_TO_SUBTYPES[ScopeType::GENERAL_SCOPE_CATEGORY][ScopeType::USER],
                    ]
            ],
            'importance' => Importance::TYPE_NORMAL,
            'triggeredBy' => Event::BY_USER,
            Event::ADDITIONAL_SETTING => [
                Event::ADDITIONAL_SETTING_HEADER_COLUMNS => [
                    EventLog::EVENT_LOG_ID => 'headers.general.id',
                    EventLog::DATE => 'headers.general.formattedDate',
                    EventLog::IMPORTANCE => 'headers.general.importance',
                    EventLog::TRIGGERED_DETAILS => 'headers.userBlocked.triggeredDetails',
                    EventLog::EVENT_TEAM => 'headers.general.eventTeam',
                    EventLog::EVENT_SOURCE => 'headers.userBlocked.eventSource',
                    EventLog::NTF_LIST => 'headers.general.notificationsList',
                ],
                Event::SETTING_EVENT_LOG => [
                    Event::ADDITIONAL_SETTING_MAPPING_FILTERS => [
                        EventLog::EVENT_LOG_ID => 'id',
                        EventLog::DATE => 'eventDate',
                        EventLog::TRIGGERED_DETAILS => 'userBy',
                        EventLog::EVENT_TEAM => 'entityTeamId',
                        EventLog::EVENT_ID => 'eventId',
                    ],
                ],
            ],
        ],
        [
            'implemented' => true,
            'name' => Event::USER_DELETED,
            'alias' => 'User deleted',
            'type' => Event::TYPE_USER,
            'entity' => Event::ENTITY_TYPE_USER,
            'scopes' => [
                ScopeType::USER =>
                    [
                        ScopeType::GENERAL_SCOPE_CATEGORY =>
                            ScopeType::SCOPE_TO_SUBTYPES[ScopeType::GENERAL_SCOPE_CATEGORY][ScopeType::USER],
                    ]
            ],
            'importance' => Importance::TYPE_NORMAL,
            'triggeredBy' => Event::BY_USER,
            Event::ADDITIONAL_SETTING => [
                Event::ADDITIONAL_SETTING_HEADER_COLUMNS => [
                    EventLog::EVENT_LOG_ID => 'headers.general.id',
                    EventLog::DATE => 'headers.general.formattedDate',
                    EventLog::IMPORTANCE => 'headers.general.importance',
                    EventLog::TRIGGERED_DETAILS => 'headers.userDeleted.triggeredDetails',
                    EventLog::EVENT_TEAM => 'headers.general.eventTeam',
                    EventLog::EVENT_SOURCE => 'headers.userDeleted.eventSource',
                    EventLog::NTF_LIST => 'headers.general.notificationsList',
                ],
                Event::SETTING_EVENT_LOG => [
                    Event::ADDITIONAL_SETTING_MAPPING_FILTERS => [
                        EventLog::EVENT_LOG_ID => 'id',
                        EventLog::DATE => 'eventDate',
                        EventLog::TRIGGERED_DETAILS => 'userBy',
                        EventLog::EVENT_TEAM => 'entityTeamId',
                        EventLog::EVENT_ID => 'eventId',
                    ],
                ],
            ],
        ],
        [
            'implemented' => true,
            'name' => Event::USER_PWD_RESET,
            'alias' => 'User password reset',
            'type' => Event::TYPE_USER,
            'entity' => Event::ENTITY_TYPE_USER,
            'scopes' => [
                ScopeType::USER =>
                    [
                        ScopeType::GENERAL_SCOPE_CATEGORY =>
                            ScopeType::SCOPE_TO_SUBTYPES[ScopeType::GENERAL_SCOPE_CATEGORY][ScopeType::USER],
                    ]
            ],
            'importance' => Importance::TYPE_NORMAL,
            'triggeredBy' => Event::BY_USER,
            Event::ADDITIONAL_SETTING => [
                Event::ADDITIONAL_SETTING_HEADER_COLUMNS => [
                    EventLog::EVENT_LOG_ID => 'headers.general.id',
                    EventLog::DATE => 'headers.general.formattedDate',
                    EventLog::IMPORTANCE => 'headers.general.importance',
                    EventLog::TRIGGERED_DETAILS => 'headers.userPwdResset.triggeredDetails',
                    EventLog::EVENT_TEAM => 'headers.general.eventTeam',
                    EventLog::EVENT_SOURCE => 'headers.userPwdResset.eventSource',
                    EventLog::NTF_LIST => 'headers.general.notificationsList',
                ],
                Event::SETTING_EVENT_LOG => [
                    Event::ADDITIONAL_SETTING_MAPPING_FILTERS => [
                        EventLog::TRIGGERED_DETAILS => 'userBy',
                        EventLog::EVENT_TEAM => 'teamBy',
                        EventLog::EVENT_ID => 'eventId',
                    ],
                ],
            ],
        ],
        [
            'implemented' => true,
            'name' => Event::USER_CHANGED_NAME,
            'alias' => 'User changed name',
            'type' => Event::TYPE_USER,
            'entity' => Event::ENTITY_TYPE_USER,
            'scopes' => [
                ScopeType::USER =>
                    [
                        ScopeType::GENERAL_SCOPE_CATEGORY =>
                            ScopeType::SCOPE_TO_SUBTYPES[ScopeType::GENERAL_SCOPE_CATEGORY][ScopeType::USER],
                    ]
            ],
            'importance' => Importance::TYPE_NORMAL,
            'triggeredBy' => Event::BY_USER,
            Event::ADDITIONAL_SETTING => [
                Event::ADDITIONAL_SETTING_HEADER_COLUMNS => [
                    EventLog::EVENT_LOG_ID => 'headers.general.id',
                    EventLog::DATE => 'headers.general.formattedDate',
                    EventLog::IMPORTANCE => 'headers.general.importance',
                    EventLog::TRIGGERED_DETAILS => 'headers.userNameChanged.triggeredDetails',
                    EventLog::EVENT_TEAM => 'headers.general.eventTeam',
                    EventLog::OLD_VALUE => 'headers.general.oldValue',
                    EventLog::EVENT_SOURCE => 'headers.userNameChanged.eventSource',
                    EventLog::NTF_LIST => 'headers.general.notificationsList',
                ],
                Event::SETTING_EVENT_LOG => [
                    Event::ADDITIONAL_SETTING_MAPPING_FILTERS => [
                        EventLog::EVENT_LOG_ID => 'id',
                        EventLog::DATE => 'eventDate',
                        EventLog::TRIGGERED_DETAILS => 'userBy',
                        EventLog::EVENT_TEAM => 'entityTeamId',
                        EventLog::EVENT_ID => 'eventId',
                    ],
                ],
            ],
        ],
        [
            'implemented' => true,
            'name' => Event::USER_CHANGED_SURNAME,
            'alias' => 'User changed surname',
            'type' => Event::TYPE_USER,
            'entity' => Event::ENTITY_TYPE_USER,
            'scopes' => [
                ScopeType::USER =>
                    [
                        ScopeType::GENERAL_SCOPE_CATEGORY =>
                            ScopeType::SCOPE_TO_SUBTYPES[ScopeType::GENERAL_SCOPE_CATEGORY][ScopeType::USER],
                    ]
            ],
            'importance' => Importance::TYPE_NORMAL,
            'triggeredBy' => Event::BY_USER
        ],
        [
            'implemented' => true,
            'name' => Event::ADMIN_USER_CREATED,
            'alias' => 'User created',
            'type' => Event::TYPE_USER,
            'entity' => Event::ENTITY_TYPE_USER,
            'scopes' => [
                ScopeType::USER =>
                    [
                        ScopeType::GENERAL_SCOPE_CATEGORY =>
                            ScopeType::SCOPE_TO_SUBTYPES[ScopeType::GENERAL_SCOPE_CATEGORY][ScopeType::ADMIN_USER],
                    ]
            ],
            'importance' => Importance::TYPE_NORMAL,
            'triggeredBy' => Event::BY_USER,
            Event::ADDITIONAL_SETTING => [
                Event::ADDITIONAL_SETTING_IS_LISTENER_TEAM => false,
                Event::ADDITIONAL_SETTING_HEADER_COLUMNS => [
                    EventLog::EVENT_LOG_ID => 'headers.general.id',
                    EventLog::DATE => 'headers.general.formattedDate',
                    EventLog::IMPORTANCE => 'headers.general.importance',
                    EventLog::TRIGGERED_DETAILS => 'headers.userCreated.triggeredDetails',
                    EventLog::EVENT_TEAM => 'headers.general.eventTeam',
                    EventLog::EVENT_SOURCE => 'headers.userCreated.eventSource',
                    EventLog::NTF_LIST => 'headers.general.notificationsList',
                ],
                Event::SETTING_EVENT_LOG => [
                    Event::ADDITIONAL_SETTING_MAPPING_FILTERS => [
                        EventLog::EVENT_LOG_ID => 'id',
                        EventLog::DATE => 'eventDate',
                        EventLog::TRIGGERED_DETAILS => 'userBy',
                        EventLog::EVENT_TEAM => 'entityTeamId',
                        EventLog::EVENT_ID => 'eventId',
                    ],
                ],
            ],
        ],
        [
            'implemented' => true,
            'name' => Event::ADMIN_USER_BLOCKED,
            'alias' => 'User blocked',
            'type' => Event::TYPE_USER,
            'entity' => Event::ENTITY_TYPE_USER,
            'scopes' => [
                ScopeType::USER =>
                    [
                        ScopeType::GENERAL_SCOPE_CATEGORY =>
                            ScopeType::SCOPE_TO_SUBTYPES[ScopeType::GENERAL_SCOPE_CATEGORY][ScopeType::ADMIN_USER],
                    ]
            ],
            'importance' => Importance::TYPE_NORMAL,
            'triggeredBy' => Event::BY_USER,
            Event::ADDITIONAL_SETTING => [
                Event::ADDITIONAL_SETTING_HEADER_COLUMNS => [
                    EventLog::EVENT_LOG_ID => 'headers.general.id',
                    EventLog::DATE => 'headers.general.formattedDate',
                    EventLog::IMPORTANCE => 'headers.general.importance',
                    EventLog::TRIGGERED_DETAILS => 'headers.userBlocked.triggeredDetails',
                    EventLog::EVENT_TEAM => 'headers.general.eventTeam',
                    EventLog::EVENT_SOURCE => 'headers.userBlocked.eventSource',
                    EventLog::NTF_LIST => 'headers.general.notificationsList',
                ],
                Event::SETTING_EVENT_LOG => [
                    Event::ADDITIONAL_SETTING_MAPPING_FILTERS => [
                        EventLog::EVENT_LOG_ID => 'id',
                        EventLog::DATE => 'eventDate',
                        EventLog::TRIGGERED_DETAILS => 'userBy',
                        EventLog::EVENT_TEAM => 'entityTeamId',
                        EventLog::EVENT_ID => 'eventId',
                    ],
                ],
            ],
        ],
        [
            'implemented' => true,
            'name' => Event::ADMIN_USER_DELETED,
            'alias' => 'User deleted',
            'type' => Event::TYPE_USER,
            'entity' => Event::ENTITY_TYPE_USER,
            'scopes' => [
                ScopeType::USER =>
                    [
                        ScopeType::GENERAL_SCOPE_CATEGORY =>
                            ScopeType::SCOPE_TO_SUBTYPES[ScopeType::GENERAL_SCOPE_CATEGORY][ScopeType::ADMIN_USER],
                    ]
            ],
            'importance' => Importance::TYPE_NORMAL,
            'triggeredBy' => Event::BY_USER,
            Event::ADDITIONAL_SETTING => [
                Event::ADDITIONAL_SETTING_HEADER_COLUMNS => [
                    EventLog::EVENT_LOG_ID => 'headers.general.id',
                    EventLog::DATE => 'headers.general.formattedDate',
                    EventLog::IMPORTANCE => 'headers.general.importance',
                    EventLog::TRIGGERED_DETAILS => 'headers.userDeleted.triggeredDetails',
                    EventLog::EVENT_TEAM => 'headers.general.eventTeam',
                    EventLog::EVENT_SOURCE => 'headers.userDeleted.eventSource',
                    EventLog::NTF_LIST => 'headers.general.notificationsList',
                ],
                Event::SETTING_EVENT_LOG => [
                    Event::ADDITIONAL_SETTING_MAPPING_FILTERS => [
                        EventLog::EVENT_LOG_ID => 'id',
                        EventLog::DATE => 'eventDate',
                        EventLog::TRIGGERED_DETAILS => 'userBy',
                        EventLog::EVENT_TEAM => 'entityTeamId',
                        EventLog::EVENT_ID => 'eventId',
                    ],
                ],
            ],
        ],
        [
            'implemented' => true,
            'name' => Event::ADMIN_USER_PWD_RESET,
            'alias' => 'User password reset',
            'type' => Event::TYPE_USER,
            'entity' => Event::ENTITY_TYPE_USER,
            'scopes' => [
                ScopeType::USER =>
                    [
                        ScopeType::GENERAL_SCOPE_CATEGORY =>
                            ScopeType::SCOPE_TO_SUBTYPES[ScopeType::GENERAL_SCOPE_CATEGORY][ScopeType::ADMIN_USER],
                    ]
            ],
            'importance' => Importance::TYPE_NORMAL,
            'triggeredBy' => Event::BY_USER,
            Event::ADDITIONAL_SETTING => [
                Event::ADDITIONAL_SETTING_HEADER_COLUMNS => [
                    EventLog::EVENT_LOG_ID => 'headers.general.id',
                    EventLog::DATE => 'headers.general.formattedDate',
                    EventLog::IMPORTANCE => 'headers.general.importance',
                    EventLog::TRIGGERED_DETAILS => 'headers.userPwdResset.triggeredDetails',
                    EventLog::EVENT_TEAM => 'headers.general.eventTeam',
                    EventLog::EVENT_SOURCE => 'headers.userPwdResset.eventSource',
                    EventLog::NTF_LIST => 'headers.general.notificationsList',
                ],
                Event::SETTING_EVENT_LOG => [
                    Event::ADDITIONAL_SETTING_MAPPING_FILTERS => [
                        EventLog::EVENT_LOG_ID => 'id',
                        EventLog::DATE => 'eventDate',
                        EventLog::TRIGGERED_DETAILS => 'userBy',
                        EventLog::EVENT_TEAM => 'entityTeamId',
                        EventLog::EVENT_ID => 'eventId',
                    ],
                ],
            ],
        ],
        [
            'implemented' => true,
            'name' => Event::ADMIN_USER_CHANGED_NAME,
            'alias' => 'User changed name',
            'type' => Event::TYPE_USER,
            'entity' => Event::ENTITY_TYPE_USER,
            'scopes' => [
                ScopeType::USER =>
                    [
                        ScopeType::GENERAL_SCOPE_CATEGORY =>
                            ScopeType::SCOPE_TO_SUBTYPES[ScopeType::GENERAL_SCOPE_CATEGORY][ScopeType::ADMIN_USER],
                    ]
            ],
            'importance' => Importance::TYPE_NORMAL,
            'triggeredBy' => Event::BY_USER,
            Event::ADDITIONAL_SETTING => [
                Event::ADDITIONAL_SETTING_HEADER_COLUMNS => [
                    EventLog::EVENT_LOG_ID => 'headers.general.id',
                    EventLog::DATE => 'headers.general.formattedDate',
                    EventLog::IMPORTANCE => 'headers.general.importance',
                    EventLog::TRIGGERED_DETAILS => 'headers.userNameChanged.triggeredDetails',
                    EventLog::EVENT_TEAM => 'headers.general.eventTeam',
                    EventLog::OLD_VALUE => 'headers.general.oldValue',
                    EventLog::EVENT_SOURCE => 'headers.userNameChanged.eventSource',
                    EventLog::NTF_LIST => 'headers.general.notificationsList',
                ],
                Event::SETTING_EVENT_LOG => [
                    Event::ADDITIONAL_SETTING_MAPPING_FILTERS => [
                        EventLog::EVENT_LOG_ID => 'id',
                        EventLog::DATE => 'eventDate',
                        EventLog::TRIGGERED_DETAILS => 'userBy',
                        EventLog::EVENT_TEAM => 'entityTeamId',
                        EventLog::EVENT_ID => 'eventId',
                    ],
                ],
            ],
        ],
        [
            'implemented' => true,
            'name' => Event::CLIENT_CREATED,
            'alias' => 'Client created',
            'type' => Event::TYPE_USER,
            'entity' => Event::ENTITY_TYPE_CLIENT,
            'scopes' => [
                ScopeType::TEAM =>
                    [
                        ScopeType::GENERAL_SCOPE_CATEGORY =>
                            ScopeType::SCOPE_TO_SUBTYPES[ScopeType::GENERAL_SCOPE_CATEGORY][ScopeType::TEAM],
                    ]
            ],
            'importance' => Importance::TYPE_NORMAL,
            'triggeredBy' => Event::BY_USER,
            Event::ADDITIONAL_SETTING => [
                Event::ADDITIONAL_SETTING_IS_LISTENER_TEAM => false,
                Event::ADDITIONAL_SETTING_HEADER_COLUMNS => [
                    EventLog::EVENT_LOG_ID => 'headers.general.id',
                    EventLog::DATE => 'headers.general.formattedDate',
                    EventLog::IMPORTANCE => 'headers.general.importance',
                    EventLog::TRIGGERED_DETAILS => 'headers.clientCreated.triggeredDetails',
                    EventLog::EVENT_TEAM => 'headers.general.eventTeam',
                    EventLog::NTF_LIST => 'headers.general.notificationsList',
                ],
                Event::SETTING_EVENT_LOG => [
                    Event::ADDITIONAL_SETTING_MAPPING_FILTERS => [
                        EventLog::EVENT_LOG_ID => 'id',
                        EventLog::DATE => 'eventDate',
                        EventLog::TRIGGERED_DETAILS => 'userBy',
                        EventLog::EVENT_TEAM => 'entityTeamId',
                        EventLog::EVENT_ID => 'eventId',
                    ],
                ],
            ],
        ],
        [
            'implemented' => true,
            'name' => Event::CLIENT_DEMO_EXPIRED,
            'alias' => 'Client demo expired',
            'type' => Event::TYPE_USER,
            'entity' => Event::ENTITY_TYPE_CLIENT,
            'scopes' => [
                ScopeType::TEAM =>
                    [
                        ScopeType::GENERAL_SCOPE_CATEGORY =>
                            ScopeType::SCOPE_TO_SUBTYPES[ScopeType::GENERAL_SCOPE_CATEGORY][ScopeType::TEAM],
                    ]
            ],
            'importance' => Importance::TYPE_NORMAL,
            'triggeredBy' => Event::BY_USER,
            Event::ADDITIONAL_SETTING => [
                Event::ADDITIONAL_SETTING_IS_LISTENER_TEAM => false,
                Event::ADDITIONAL_SETTING_HEADER_COLUMNS => [
                    EventLog::EVENT_LOG_ID => 'headers.general.id',
                    EventLog::DATE => 'headers.general.formattedDate',
                    EventLog::IMPORTANCE => 'headers.general.importance',
                    EventLog::EVENT_TEAM => 'headers.general.eventTeam',
                    EventLog::NTF_LIST => 'headers.general.notificationsList',
                ],
                Event::SETTING_EVENT_LOG => [
                    Event::ADDITIONAL_SETTING_MAPPING_FILTERS => [
                        EventLog::EVENT_LOG_ID => 'id',
                        EventLog::DATE => 'eventDate',
                        EventLog::EVENT_TEAM => 'entityTeamId',
                        EventLog::EVENT_ID => 'eventId',
                    ],
                ],
            ],
        ],
        [
            'implemented' => true,
            'name' => Event::CLIENT_BLOCKED,
            'alias' => 'Client blocked',
            'type' => Event::TYPE_USER,
            'entity' => Event::ENTITY_TYPE_CLIENT,
            'scopes' => [
                ScopeType::TEAM =>
                    [
                        ScopeType::GENERAL_SCOPE_CATEGORY =>
                            ScopeType::SCOPE_TO_SUBTYPES[ScopeType::GENERAL_SCOPE_CATEGORY][ScopeType::TEAM],
                    ]
            ],
            'importance' => Importance::TYPE_NORMAL,
            'triggeredBy' => Event::BY_USER,
            Event::ADDITIONAL_SETTING => [
                Event::ADDITIONAL_SETTING_IS_LISTENER_TEAM => false,
                Event::ADDITIONAL_SETTING_HEADER_COLUMNS => [
                    EventLog::EVENT_LOG_ID => 'headers.general.id',
                    EventLog::DATE => 'headers.general.formattedDate',
                    EventLog::IMPORTANCE => 'headers.general.importance',
                    EventLog::TRIGGERED_DETAILS => 'headers.clientCreated.triggeredDetails',
                    EventLog::EVENT_TEAM => 'headers.general.eventTeam',
                    EventLog::NTF_LIST => 'headers.general.notificationsList',
                ],

                Event::SETTING_EVENT_LOG => [
                    Event::ADDITIONAL_SETTING_MAPPING_FILTERS => [
                        EventLog::EVENT_LOG_ID => 'id',
                        EventLog::DATE => 'eventDate',
                        EventLog::TRIGGERED_DETAILS => 'userBy',
                        EventLog::EVENT_TEAM => 'entityTeamId',
                        EventLog::EVENT_ID => 'eventId',
                    ],
                ],
            ],
        ],
        [
            'implemented' => true,
            'name' => Event::DEVICE_UNKNOWN_DETECTED,
            'alias' => 'Device unknown detected',
            'type' => Event::TYPE_USER,
            'entity' => Event::ENTITY_TYPE_UNKNOWN_DEVICE_AUTH,
            'scopes' => [
                ScopeType::DEVICE =>
                    [
                        ScopeType::GENERAL_SCOPE_CATEGORY =>
                            ScopeType::SCOPE_TO_SUBTYPES[ScopeType::GENERAL_SCOPE_CATEGORY][ScopeType::DEVICE],
                    ]
            ],
            Event::ADDITIONAL_SETTING => [
                Event::ADDITIONAL_SETTING_IS_LISTENER_TEAM => false,
                Event::ADDITIONAL_SETTING_HEADER_COLUMNS => [
                    EventLog::EVENT_LOG_ID => 'headers.general.id',
                    EventLog::DATE => 'headers.general.formattedDate',
                    EventLog::IMPORTANCE => 'headers.general.importance',
                    EventLog::EVENT_SOURCE => 'headers.deviceInfo.eventSource',
                    EventLog::NTF_LIST => 'headers.general.notificationsList',
                ],
                Event::SETTING_EVENT_LOG => [
                    Event::ADDITIONAL_SETTING_MAPPING_FILTERS => [
                        EventLog::EVENT_LOG_ID => 'id',
                        EventLog::DATE => 'eventDate',
                        EventLog::EVENT_ID => 'eventId',
                    ],
                ],
            ],
            'importance' => Importance::TYPE_NORMAL,
        ],
        [
            'implemented' => true,
            'name' => Event::DEVICE_IN_STOCK,
            'alias' => 'Device in stock',
            'type' => Event::TYPE_USER,
            'entity' => Event::ENTITY_TYPE_DEVICE,
            'scopes' => [
                ScopeType::DEVICE =>
                    [
                        ScopeType::GENERAL_SCOPE_CATEGORY =>
                            ScopeType::SCOPE_TO_SUBTYPES[ScopeType::GENERAL_SCOPE_CATEGORY][ScopeType::DEVICE],
                    ]
            ],
            'importance' => Importance::TYPE_NORMAL,
            Event::ADDITIONAL_SETTING => [
                Event::ADDITIONAL_SETTING_HEADER_COLUMNS => [
                    EventLog::EVENT_LOG_ID => 'headers.general.id',
                    EventLog::DATE => 'headers.general.formattedDate',
                    EventLog::IMPORTANCE => 'headers.general.importance',
                    EventLog::TRIGGERED_DETAILS => 'headers.deviceInfo.triggeredDetails',
                    EventLog::EVENT_TEAM => 'headers.general.eventTeam',
                    EventLog::EVENT_SOURCE => 'headers.deviceInfo.eventSource',
                    EventLog::NTF_LIST => 'headers.general.notificationsList',
                ],
                Event::SETTING_EVENT_LOG => [
                    Event::ADDITIONAL_SETTING_MAPPING_FILTERS => [
                        EventLog::EVENT_LOG_ID => 'id',
                        EventLog::DATE => 'eventDate',
                        EventLog::TRIGGERED_DETAILS => 'userBy',
                        EventLog::EVENT_TEAM => 'entityTeamId',
                        EventLog::EVENT_SOURCE => 'entityId',
                        EventLog::EVENT_ID => 'eventId',
                    ],
                ],
            ],
            'triggeredBy' => Event::BY_USER
        ],
        [
            'implemented' => true,
            'name' => Event::DEVICE_OFFLINE,
            'alias' => 'Device offline',
            'type' => Event::TYPE_USER,
            'entity' => Event::ENTITY_TYPE_DEVICE,
            'scopes' => [
                ScopeType::DEVICE =>
                    [
                        ScopeType::GENERAL_SCOPE_CATEGORY =>
                            ScopeType::SCOPE_TO_SUBTYPES[ScopeType::GENERAL_SCOPE_CATEGORY][ScopeType::DEVICE],
                    ]
            ],
            'importance' => Importance::TYPE_NORMAL,
            Event::ADDITIONAL_SETTING => [
                Event::ADDITIONAL_SETTING_HEADER_COLUMNS => [
                    EventLog::EVENT_LOG_ID => 'headers.general.id',
                    EventLog::DATE => 'headers.general.formattedDate',
                    EventLog::IMPORTANCE => 'headers.general.importance',
                    EventLog::EVENT_TEAM => 'headers.general.eventTeam',
                    EventLog::EVENT_SOURCE => 'headers.deviceInfo.eventSource',
                    EventLog::NTF_LIST => 'headers.general.notificationsList',
                ],

                Event::SETTING_EVENT_LOG => [
                    Event::ADDITIONAL_SETTING_MAPPING_FILTERS => [
                        EventLog::EVENT_LOG_ID => 'id',
                        EventLog::DATE => 'eventDate',
                        EventLog::EVENT_TEAM => 'entityTeamId',
                        EventLog::EVENT_SOURCE => 'entityId',
                        EventLog::EVENT_ID => 'eventId',
                    ],
                ],
            ],
            'triggeredBy' => Event::BY_USER
        ],
        [
            'implemented' => true,
            'name' => Event::DEVICE_UNAVAILABLE,
            'alias' => 'Device unavailable',
            'type' => Event::TYPE_USER,
            'entity' => Event::ENTITY_TYPE_DEVICE,
            'scopes' => [
                ScopeType::DEVICE =>
                    [
                        ScopeType::GENERAL_SCOPE_CATEGORY =>
                            ScopeType::SCOPE_TO_SUBTYPES[ScopeType::GENERAL_SCOPE_CATEGORY][ScopeType::DEVICE],
                    ]
            ],
            'importance' => Importance::TYPE_NORMAL,
            Event::ADDITIONAL_SETTING => [
                Event::ADDITIONAL_SETTING_HEADER_COLUMNS => [
                    EventLog::EVENT_LOG_ID => 'headers.general.id',
                    EventLog::DATE => 'headers.general.formattedDate',
                    EventLog::IMPORTANCE => 'headers.general.importance',
                    EventLog::TRIGGERED_DETAILS => 'headers.deviceInfo.triggeredDetails',
                    EventLog::EVENT_TEAM => 'headers.general.eventTeam',
                    EventLog::EVENT_SOURCE => 'headers.deviceInfo.eventSource',
                    EventLog::NTF_LIST => 'headers.general.notificationsList',
                ],
                Event::SETTING_EVENT_LOG => [
                    Event::ADDITIONAL_SETTING_MAPPING_FILTERS => [
                        EventLog::EVENT_LOG_ID => 'id',
                        EventLog::DATE => 'eventDate',
                        EventLog::TRIGGERED_DETAILS => 'userBy',
                        EventLog::EVENT_TEAM => 'entityTeamId',
                        EventLog::EVENT_SOURCE => 'entityId',
                        EventLog::EVENT_ID => 'eventId',
                    ],
                ],
            ],
            'triggeredBy' => Event::BY_USER
        ],
        [
            'implemented' => true,
            'name' => Event::DEVICE_DELETED,
            'alias' => 'Device deleted',
            'type' => Event::TYPE_USER,
            'entity' => Event::ENTITY_TYPE_DEVICE,
            'scopes' => [
                ScopeType::DEVICE =>
                    [
                        ScopeType::GENERAL_SCOPE_CATEGORY =>
                            ScopeType::SCOPE_TO_SUBTYPES[ScopeType::GENERAL_SCOPE_CATEGORY][ScopeType::DEVICE],
                    ]
            ],
            'importance' => Importance::TYPE_NORMAL,
            Event::ADDITIONAL_SETTING => [
                Event::ADDITIONAL_SETTING_HEADER_COLUMNS => [
                    EventLog::EVENT_LOG_ID => 'headers.general.id',
                    EventLog::DATE => 'headers.general.formattedDate',
                    EventLog::IMPORTANCE => 'headers.general.importance',
                    EventLog::TRIGGERED_DETAILS => 'headers.deviceInfo.triggeredDetails',
                    EventLog::EVENT_TEAM => 'headers.general.eventTeam',
                    EventLog::EVENT_SOURCE => 'headers.deviceInfo.eventSource',
                    EventLog::NTF_LIST => 'headers.general.notificationsList',
                ],
                Event::SETTING_EVENT_LOG => [
                    Event::ADDITIONAL_SETTING_MAPPING_FILTERS => [
                        EventLog::EVENT_LOG_ID => 'id',
                        EventLog::DATE => 'eventDate',
                        EventLog::TRIGGERED_DETAILS => 'userBy',
                        EventLog::EVENT_TEAM => 'entityTeamId',
                        EventLog::EVENT_SOURCE => 'entityId',
                        EventLog::EVENT_ID => 'eventId',
                    ],
                ],
            ],
            'triggeredBy' => Event::BY_USER
        ],
        [
            'implemented' => true,
            'name' => Event::DEVICE_REPLACED,
            'alias' => 'Device replaced',
            'type' => Event::TYPE_USER,
            'entity' => Event::ENTITY_TYPE_DEVICE,
            'scopes' => [
                ScopeType::DEVICE =>
                    [
                        ScopeType::GENERAL_SCOPE_CATEGORY =>
                            ScopeType::SCOPE_TO_SUBTYPES[ScopeType::GENERAL_SCOPE_CATEGORY][ScopeType::DEVICE],
                    ]
            ],
            'importance' => Importance::TYPE_NORMAL,
            Event::ADDITIONAL_SETTING => [
                Event::ADDITIONAL_SETTING_HEADER_COLUMNS => [
                    EventLog::EVENT_LOG_ID => 'headers.general.id',
                    EventLog::DATE => 'headers.general.formattedDate',
                    EventLog::IMPORTANCE => 'headers.general.importance',
                    EventLog::TRIGGERED_DETAILS => 'headers.deviceInfo.triggeredDetails',
                    EventLog::EVENT_TEAM => 'headers.general.eventTeam',
                    EventLog::EVENT_SOURCE => 'headers.deviceInfo.eventSource',
                    EventLog::NTF_LIST => 'headers.general.notificationsList',
                ],
                Event::SETTING_EVENT_LOG => [
                    Event::ADDITIONAL_SETTING_MAPPING_FILTERS => [
                        EventLog::EVENT_LOG_ID => 'id',
                        EventLog::DATE => 'eventDate',
                        EventLog::TRIGGERED_DETAILS => 'userBy',
                        EventLog::EVENT_TEAM => 'entityTeamId',
                        EventLog::EVENT_SOURCE => 'entityId',
                        EventLog::EVENT_ID => 'eventId',
                    ],
                ],
            ],
            'triggeredBy' => Event::BY_USER
        ],
        [
            'implemented' => true,
            'name' => Event::LOGIN_AS_CLIENT,
            'alias' => 'Login as client',
            'type' => Event::TYPE_USER,
            'entity' => Event::ENTITY_TYPE_USER,
            'scopes' => [
                ScopeType::USER =>
                    [
                        ScopeType::GENERAL_SCOPE_CATEGORY =>
                            ScopeType::SCOPE_TO_SUBTYPES[ScopeType::GENERAL_SCOPE_CATEGORY][ScopeType::USER],
                    ]
            ],
            'importance' => Importance::TYPE_NORMAL,
            'triggeredBy' => Event::BY_USER,
            Event::ADDITIONAL_SETTING => [
                Event::ADDITIONAL_SETTING_HEADER_COLUMNS => [
                    EventLog::EVENT_LOG_ID => 'headers.general.id',
                    EventLog::DATE => 'headers.general.formattedDate',
                    EventLog::IMPORTANCE => 'headers.general.importance',
                    EventLog::TRIGGERED_DETAILS => 'headers.loginAsUser.triggeredDetails',
                    EventLog::EVENT_TEAM => 'headers.general.eventTeam',
                    EventLog::EVENT_SOURCE => 'headers.loginAsUser.eventSource',
                    EventLog::NTF_LIST => 'headers.general.notificationsList',
                ]
            ],
        ],
        [
            'implemented' => true,
            'name' => Event::LOGIN_AS_USER,
            'alias' => 'Login as user',
            'type' => Event::TYPE_USER,
            'entity' => Event::ENTITY_TYPE_USER,
            'scopes' => [
                ScopeType::USER =>
                    [
                        ScopeType::GENERAL_SCOPE_CATEGORY =>
                            ScopeType::SCOPE_TO_SUBTYPES[ScopeType::GENERAL_SCOPE_CATEGORY][ScopeType::ADMIN_USER],
                    ]
            ],
            'importance' => Importance::TYPE_NORMAL,
            'triggeredBy' => Event::BY_USER,
            Event::ADDITIONAL_SETTING => [
                Event::ADDITIONAL_SETTING_IS_LISTENER_TEAM => false,
                Event::ADDITIONAL_SETTING_HEADER_COLUMNS => [
                    EventLog::EVENT_LOG_ID => 'headers.general.id',
                    EventLog::DATE => 'headers.general.formattedDate',
                    EventLog::IMPORTANCE => 'headers.general.importance',
                    EventLog::TRIGGERED_DETAILS => 'headers.loginAsUser.triggeredDetails',
                    EventLog::EVENT_TEAM => 'headers.general.eventTeam',
                    EventLog::EVENT_SOURCE => 'headers.loginAsUser.eventSource',
                    EventLog::NTF_LIST => 'headers.general.notificationsList',
                ],
                Event::SETTING_EVENT_LOG => [
                    Event::ADDITIONAL_SETTING_MAPPING_FILTERS => [
                        EventLog::EVENT_LOG_ID => 'id',
                        EventLog::DATE => 'eventDate',
                        EventLog::TRIGGERED_DETAILS => 'userBy',
                        EventLog::EVENT_TEAM => 'entityTeamId',
                        EventLog::EVENT_ID => 'eventId',
                    ],
                ],
            ],
        ],
        [
            'implemented' => true,
            'name' => Event::VEHICLE_GEOFENCE_ENTER,
            'alias' => 'Vehicle entered area',
            'type' => Event::TYPE_USER,
            'entity' => Event::ENTITY_TYPE_AREA_HISTORY,
            'scopes' => [
                ScopeType::AREA_HISTORY =>
                    [
                        ScopeType::GENERAL_SCOPE_CATEGORY =>
                            ScopeType::SCOPE_TO_SUBTYPES[ScopeType::GENERAL_SCOPE_CATEGORY][ScopeType::AREA_HISTORY],
                        ScopeType::ADDITIONAL_SCOPE_CATEGORY =>
                            ScopeType::SCOPE_TO_SUBTYPES[ScopeType::ADDITIONAL_SCOPE_CATEGORY][ScopeType::AREA_HISTORY],
                    ]
            ],
            'importance' => Importance::TYPE_NORMAL,
            Event::ADDITIONAL_SETTING => [
                Event::ADDITIONAL_SETTING_HEADER_COLUMNS => [
                    EventLog::EVENT_LOG_ID => 'headers.general.id',
                    EventLog::DATE => 'headers.general.formattedDate',
                    EventLog::IMPORTANCE => 'headers.general.importance',
                    EventLog::EVENT_TEAM => 'headers.general.eventTeam',
                    EventLog::TRIGGERED_DETAILS => 'headers.trackerInfo.triggeredDetails',
                    EventLog::EVENT_SOURCE => 'headers.trackerInfo.eventSource',
                    EventLog::AREAS => 'headers.trackerInfo.areas',
                    EventLog::SHORT_DETAILS => 'headers.trackerInfo.shortDetails',
                    EventLog::DEVICE_IMEI => 'headers.trackerInfo.imei',
                    EventLog::NTF_LIST => 'headers.general.notificationsList',
                ],
                Event::SETTING_EVENT_LOG => [
                    Event::ADDITIONAL_SETTING_MAPPING_FILTERS => [
                        EventLog::EVENT_ID => 'eventId',
                        EventLog::EVENT_LOG_ID => 'id',
                        EventLog::DATE => 'eventDate',
                        EventLog::TRIGGERED_DETAILS => 'driverId',
                        EventLog::EVENT_TEAM => 'entityTeamId',
                    ],
                ],
            ],
            'triggeredBy' => Event::BY_GEO
        ],
        [
            'implemented' => true,
            'name' => Event::VEHICLE_GEOFENCE_LEAVE,
            'alias' => 'Vehicle left area',
            'type' => Event::TYPE_USER,
            'entity' => Event::ENTITY_TYPE_AREA_HISTORY,
            'scopes' => [
                ScopeType::AREA_HISTORY =>
                    [
                        ScopeType::GENERAL_SCOPE_CATEGORY =>
                            ScopeType::SCOPE_TO_SUBTYPES[ScopeType::GENERAL_SCOPE_CATEGORY][ScopeType::AREA_HISTORY],
                        ScopeType::ADDITIONAL_SCOPE_CATEGORY =>
                            ScopeType::SCOPE_TO_SUBTYPES[ScopeType::ADDITIONAL_SCOPE_CATEGORY][ScopeType::AREA_HISTORY],
                    ]
            ],
            'importance' => Importance::TYPE_NORMAL,
            Event::ADDITIONAL_SETTING => [
                Event::ADDITIONAL_SETTING_HEADER_COLUMNS => [
                    EventLog::EVENT_LOG_ID => 'headers.general.id',
                    EventLog::DATE => 'headers.general.formattedDate',
                    EventLog::IMPORTANCE => 'headers.general.importance',
                    EventLog::EVENT_TEAM => 'headers.general.eventTeam',
                    EventLog::TRIGGERED_DETAILS => 'headers.trackerInfo.triggeredDetails',
                    EventLog::EVENT_SOURCE => 'headers.trackerInfo.eventSource',
                    EventLog::AREAS => 'headers.trackerInfo.areas',
                    EventLog::SHORT_DETAILS => 'headers.trackerInfo.shortDetails',
                    EventLog::DEVICE_IMEI => 'headers.trackerInfo.imei',
                    EventLog::NTF_LIST => 'headers.general.notificationsList',
                ],
                Event::SETTING_EVENT_LOG => [
                    Event::ADDITIONAL_SETTING_MAPPING_FILTERS => [
                        EventLog::EVENT_ID => 'eventId',
                        EventLog::EVENT_LOG_ID => 'id',
                        EventLog::DATE => 'eventDate',
                        EventLog::TRIGGERED_DETAILS => 'driverId',
                        EventLog::EVENT_TEAM => 'entityTeamId',
                    ],
                ],
            ],
            'triggeredBy' => Event::BY_GEO
        ],
        [
            'implemented' => true,
            'name' => Event::VEHICLE_OVERSPEEDING_INSIDE_GEOFENCE,
            'alias' => 'Vehicle overspeeding inside area',
            'type' => Event::TYPE_USER,
            'entity' => Event::ENTITY_TYPE_AREA_HISTORY,
            'scopes' => [
                ScopeType::AREA_HISTORY =>
                    [
                        ScopeType::GENERAL_SCOPE_CATEGORY =>
                            ScopeType::SCOPE_TO_SUBTYPES[ScopeType::GENERAL_SCOPE_CATEGORY][ScopeType::AREA_HISTORY],
                        ScopeType::ADDITIONAL_SCOPE_CATEGORY =>
                            ScopeType::SCOPE_TO_SUBTYPES[ScopeType::ADDITIONAL_SCOPE_CATEGORY][ScopeType::AREA_HISTORY],
                    ]
            ],
            'importance' => Importance::TYPE_NORMAL,
            Event::ADDITIONAL_SETTING => [
                Event::ADDITIONAL_SETTING_IS_OVER_SPEEDING => true,
                Event::ADDITIONAL_SETTING_HEADER_COLUMNS => [
                    EventLog::EVENT_LOG_ID => 'headers.general.id',
                    EventLog::DATE => 'headers.general.formattedDate',
                    EventLog::IMPORTANCE => 'headers.general.importance',
                    EventLog::EVENT_TEAM => 'headers.general.eventTeam',
                    EventLog::TRIGGERED_DETAILS => 'headers.trackerInfo.triggeredDetails',
                    EventLog::EVENT_SOURCE => 'headers.trackerInfo.eventSource',
                    EventLog::AREAS => 'headers.trackerInfo.areas',
                    EventLog::SHORT_DETAILS => 'headers.trackerInfo.shortDetails',
                    EventLog::DEVICE_IMEI => 'headers.trackerInfo.imei',
                    EventLog::MAX_SPEED => 'headers.trackerInfo.maxSpeed',
                    EventLog::LIMIT => 'headers.general.limit',
                    EventLog::NTF_LIST => 'headers.general.notificationsList',
                ],
                Event::SETTING_EVENT_LOG => [
                    Event::ADDITIONAL_SETTING_MAPPING_FILTERS => [
                        EventLog::EVENT_ID => 'eventId',
                        EventLog::EVENT_LOG_ID => 'id',
                        EventLog::DATE => 'eventDate',
                        EventLog::TRIGGERED_DETAILS => 'vehicleId',
                        EventLog::EVENT_SOURCE => 'driverId',
                        EventLog::EVENT_TEAM => 'entityTeamId',
                    ],
                ],
            ],
            'triggeredBy' => Event::BY_GEO
        ],
        [
            'implemented' => true,
            'name' => Event::VEHICLE_OVERSPEEDING,
            'alias' => 'Maximum Speed',
            'type' => Event::TYPE_USER,
            'entity' => Event::ENTITY_TYPE_TRACKER_HISTORY,
            'scopes' => [
                ScopeType::VEHICLE =>
                    [
                        ScopeType::GENERAL_SCOPE_CATEGORY =>
                            ScopeType::SCOPE_TO_SUBTYPES[ScopeType::GENERAL_SCOPE_CATEGORY][ScopeType::VEHICLE],
                    ],
                ScopeType::AREA =>
                    [
                        ScopeType::ADDITIONAL_SCOPE_CATEGORY =>
                            ScopeType::SCOPE_TO_SUBTYPES[ScopeType::ADDITIONAL_SCOPE_CATEGORY][ScopeType::AREA]
                    ]
            ],
            'importance' => Importance::TYPE_NORMAL,
            Event::ADDITIONAL_SETTING => [
                Event::ADDITIONAL_SETTING_IS_OVER_SPEEDING => true,
                Event::ADDITIONAL_SETTING_IS_TIME_DURATION => true,
                Event::ADDITIONAL_SETTING_IS_DISTANCE => true,
                Event::ADDITIONAL_SETTING_IS_EXPRESSION_OPERATOR => true,
                Event::ADDITIONAL_SETTING_IS_AREA_TRIGGER_TYPE => true,
                Event::ADDITIONAL_SETTING_HEADER_COLUMNS => [
                    EventLog::EVENT_LOG_ID => 'headers.general.id',
                    EventLog::DATE => 'headers.general.formattedDate',
                    EventLog::IMPORTANCE => 'headers.general.importance',
                    EventLog::EVENT_TEAM => 'headers.general.eventTeam',
                    EventLog::TRIGGERED_DETAILS => 'headers.trackerInfo.triggeredDetails',
                    EventLog::EVENT_SOURCE => 'headers.trackerInfo.eventSource',
                    EventLog::SHORT_DETAILS => 'headers.trackerInfo.shortDetails',
                    EventLog::DEVICE_IMEI => 'headers.trackerInfo.imei',
                    EventLog::AREAS => 'headers.trackerInfo.areas',
                    EventLog::MAX_SPEED => 'headers.trackerInfo.maxSpeed',
                    EventLog::DURATION => 'headers.trackerInfo.duration',
                    EventLog::DISTANCE => 'headers.trackerInfo.distance',
                    EventLog::LIMIT => 'headers.general.limit',
                    EventLog::ADDRESS => 'headers.trackerInfo.address',
                    EventLog::NTF_LIST => 'headers.general.notificationsList',
                ],
                Event::SETTING_EVENT_LOG => [
                    Event::ADDITIONAL_SETTING_MAPPING_FILTERS => [
                        EventLog::EVENT_ID => 'eventId',
                        EventLog::EVENT_LOG_ID => 'id',
                        EventLog::DATE => 'eventDate',
                        EventLog::EVENT_TEAM => 'entityTeamId',
                        EventLog::TRIGGERED_DETAILS => 'driverId',
                        EventLog::EVENT_SOURCE => 'vehicleId',
                    ],
                ],
            ],
            'triggeredBy' => Event::BY_DRIVER
        ],
        [
            'implemented' => true,
            'name' => Event::VEHICLE_LONG_STANDING,
            'alias' => 'Standing longer than defined',
            'type' => Event::TYPE_USER,
            'entity' => Event::ENTITY_TYPE_TRACKER_HISTORY,
            'scopes' => [
                ScopeType::VEHICLE =>
                    [
                        ScopeType::GENERAL_SCOPE_CATEGORY =>
                            ScopeType::SCOPE_TO_SUBTYPES[ScopeType::GENERAL_SCOPE_CATEGORY][ScopeType::VEHICLE],
                    ],
                ScopeType::AREA =>
                    [
                        ScopeType::ADDITIONAL_SCOPE_CATEGORY =>
                            ScopeType::SCOPE_TO_SUBTYPES[ScopeType::ADDITIONAL_SCOPE_CATEGORY][ScopeType::AREA]
                    ]
            ],
            'importance' => Importance::TYPE_NORMAL,
            Event::ADDITIONAL_SETTING => [
                Event::ADDITIONAL_SETTING_IS_TIME_DURATION => true,
                Event::ADDITIONAL_SETTING_IS_AREA_TRIGGER_TYPE => true,
                Event::ADDITIONAL_SETTING_HEADER_COLUMNS => [
                    EventLog::EVENT_LOG_ID => 'headers.general.id',
                    EventLog::DATE => 'headers.general.formattedDate',
                    EventLog::IMPORTANCE => 'headers.general.importance',
                    EventLog::EVENT_TEAM => 'headers.general.eventTeam',
                    EventLog::TRIGGERED_DETAILS => 'headers.trackerInfo.triggeredDetails',
                    EventLog::EVENT_SOURCE => 'headers.trackerInfo.eventSource',
                    EventLog::SHORT_DETAILS => 'headers.trackerInfo.shortDetails',
                    EventLog::DEVICE_IMEI => 'headers.trackerInfo.imei',
                    EventLog::AREAS => 'headers.trackerInfo.areas',
                    EventLog::DURATION => 'headers.trackerInfo.duration',
                    EventLog::LIMIT => 'headers.general.limit',
                    EventLog::ADDRESS => 'headers.trackerInfo.address',
                    EventLog::NTF_LIST => 'headers.general.notificationsList',
                ],
                Event::SETTING_EVENT_LOG => [
                    Event::ADDITIONAL_SETTING_MAPPING_FILTERS => [
                        EventLog::EVENT_ID => 'eventId',
                        EventLog::EVENT_LOG_ID => 'id',
                        EventLog::DATE => 'eventDate',
                        EventLog::EVENT_TEAM => 'entityTeamId',
                        EventLog::TRIGGERED_DETAILS => 'driverId',
                        EventLog::EVENT_SOURCE => 'vehicleId',
                    ],
                ],
            ],
            'triggeredBy' => Event::BY_DRIVER
        ],
        [
            'implemented' => true,
            'name' => Event::VEHICLE_LONG_DRIVING,
            'alias' => 'Driving longer than defined',
            'type' => Event::TYPE_USER,
            'entity' => Event::ENTITY_TYPE_TRACKER_HISTORY,
            'scopes' => [
                ScopeType::VEHICLE =>
                    [
                        ScopeType::GENERAL_SCOPE_CATEGORY =>
                            ScopeType::SCOPE_TO_SUBTYPES[ScopeType::GENERAL_SCOPE_CATEGORY][ScopeType::VEHICLE],
                    ],
                ScopeType::AREA =>
                    [
                        ScopeType::ADDITIONAL_SCOPE_CATEGORY =>
                            ScopeType::SCOPE_TO_SUBTYPES[ScopeType::ADDITIONAL_SCOPE_CATEGORY][ScopeType::AREA]
                    ]
            ],
            'importance' => Importance::TYPE_NORMAL,
            Event::ADDITIONAL_SETTING => [
                Event::ADDITIONAL_SETTING_IS_TIME_DURATION => true,
                Event::ADDITIONAL_SETTING_IS_DISTANCE => true,
                Event::ADDITIONAL_SETTING_IS_EXPRESSION_OPERATOR => true,
                Event::ADDITIONAL_SETTING_IS_AREA_TRIGGER_TYPE => true,
                Event::ADDITIONAL_SETTING_HEADER_COLUMNS => [
                    EventLog::EVENT_LOG_ID => 'headers.general.id',
                    EventLog::DATE => 'headers.general.formattedDate',
                    EventLog::IMPORTANCE => 'headers.general.importance',
                    EventLog::EVENT_TEAM => 'headers.general.eventTeam',
                    EventLog::TRIGGERED_DETAILS => 'headers.trackerInfo.triggeredDetails',
                    EventLog::EVENT_SOURCE => 'headers.trackerInfo.eventSource',
                    EventLog::SHORT_DETAILS => 'headers.trackerInfo.shortDetails',
                    EventLog::DEVICE_IMEI => 'headers.trackerInfo.imei',
                    EventLog::AREAS => 'headers.trackerInfo.areas',
                    EventLog::DURATION => 'headers.trackerInfo.duration',
                    EventLog::DISTANCE => 'headers.trackerInfo.distance',
                    EventLog::LIMIT => 'headers.general.limit',
                    EventLog::ADDRESS => 'headers.trackerInfo.address',
                    EventLog::NTF_LIST => 'headers.general.notificationsList',
                ],
                Event::SETTING_EVENT_LOG => [
                    Event::ADDITIONAL_SETTING_MAPPING_FILTERS => [
                        EventLog::EVENT_ID => 'eventId',
                        EventLog::EVENT_LOG_ID => 'id',
                        EventLog::DATE => 'eventDate',
                        EventLog::EVENT_TEAM => 'entityTeamId',
                        EventLog::TRIGGERED_DETAILS => 'driverId',
                        EventLog::EVENT_SOURCE => 'vehicleId',
                    ],
                ],
            ],
            'triggeredBy' => Event::BY_DRIVER
        ],
        [
            'implemented' => true,
            'name' => Event::VEHICLE_MOVING,
            'alias' => 'Vehicle is moving',
            'type' => Event::TYPE_USER,
            'entity' => Event::ENTITY_TYPE_TRACKER_HISTORY,
            'scopes' => [
                ScopeType::VEHICLE =>
                    [
                        ScopeType::GENERAL_SCOPE_CATEGORY =>
                            ScopeType::SCOPE_TO_SUBTYPES[ScopeType::GENERAL_SCOPE_CATEGORY][ScopeType::VEHICLE],
                    ],
                ScopeType::AREA =>
                    [
                        ScopeType::ADDITIONAL_SCOPE_CATEGORY =>
                            ScopeType::SCOPE_TO_SUBTYPES[ScopeType::ADDITIONAL_SCOPE_CATEGORY][ScopeType::AREA]
                    ]
            ],
            'importance' => Importance::TYPE_NORMAL,
            Event::ADDITIONAL_SETTING => [
                Event::ADDITIONAL_SETTING_IS_TIME_DURATION => true,
                Event::ADDITIONAL_SETTING_IS_DISTANCE => true,
                Event::ADDITIONAL_SETTING_IS_EXPRESSION_OPERATOR => true,
                Event::ADDITIONAL_SETTING_IS_AREA_TRIGGER_TYPE => true,
                Event::ADDITIONAL_SETTING_HEADER_COLUMNS => [
                    EventLog::EVENT_LOG_ID => 'headers.general.id',
                    EventLog::DATE => 'headers.general.formattedDate',
                    EventLog::IMPORTANCE => 'headers.general.importance',
                    EventLog::EVENT_TEAM => 'headers.general.eventTeam',
                    EventLog::EVENT_SOURCE => 'headers.trackerInfo.eventSource',
                    EventLog::SHORT_DETAILS => 'headers.trackerInfo.shortDetails',
                    EventLog::DEVICE_IMEI => 'headers.trackerInfo.imei',
                    EventLog::AREAS => 'headers.trackerInfo.areas',
                    EventLog::DURATION => 'headers.trackerInfo.duration',
                    EventLog::DISTANCE => 'headers.trackerInfo.distance',
                    EventLog::LIMIT => 'headers.general.limit',
                    EventLog::ADDRESS => 'headers.trackerInfo.address',
                    EventLog::NTF_LIST => 'headers.general.notificationsList',
                ],
                Event::SETTING_EVENT_LOG => [
                    Event::ADDITIONAL_SETTING_MAPPING_FILTERS => [
                        EventLog::EVENT_ID => 'eventId',
                        EventLog::EVENT_LOG_ID => 'id',
                        EventLog::DATE => 'eventDate',
                        EventLog::EVENT_TEAM => 'entityTeamId',
                        EventLog::EVENT_SOURCE => 'vehicleId',
                    ],
                ],
            ],
            'triggeredBy' => Event::BY_DRIVER
        ],
        [
            'implemented' => true,
            'name' => Event::VEHICLE_EXCESSING_IDLING,
            'alias' => 'Excess Idling',
            'type' => Event::TYPE_USER,
            'entity' => Event::ENTITY_TYPE_IDLING,
            'scopes' => [
                ScopeType::VEHICLE =>
                    [
                        ScopeType::GENERAL_SCOPE_CATEGORY =>
                            ScopeType::SCOPE_TO_SUBTYPES[ScopeType::GENERAL_SCOPE_CATEGORY][ScopeType::VEHICLE],
                    ],
                ScopeType::AREA =>
                    [
                        ScopeType::ADDITIONAL_SCOPE_CATEGORY =>
                            ScopeType::SCOPE_TO_SUBTYPES[ScopeType::ADDITIONAL_SCOPE_CATEGORY][ScopeType::AREA]
                    ]
            ],
            'importance' => Importance::TYPE_NORMAL,
            Event::ADDITIONAL_SETTING => [
                Event::ADDITIONAL_SETTING_IS_TIME_DURATION => true,
                Event::ADDITIONAL_SETTING_IS_AREA_TRIGGER_TYPE => true,
                Event::ADDITIONAL_SETTING_HEADER_COLUMNS => [
                    EventLog::EVENT_LOG_ID => 'headers.general.id',
                    EventLog::DATE => 'headers.general.formattedDate',
                    EventLog::IMPORTANCE => 'headers.general.importance',
                    EventLog::TRIGGERED_DETAILS => 'headers.trackerInfo.triggeredDetails',
                    EventLog::EVENT_TEAM => 'headers.general.eventTeam',
                    EventLog::EVENT_SOURCE => 'headers.trackerInfo.eventSource',
                    EventLog::AREAS => 'headers.trackerInfo.areas',
                    EventLog::LIMIT => 'headers.general.limit',
                    EventLog::NTF_LIST => 'headers.general.notificationsList',
                ],
                Event::SETTING_EVENT_LOG => [
                    Event::ADDITIONAL_SETTING_MAPPING_FILTERS => [
                        EventLog::EVENT_ID => 'eventId',
                        EventLog::EVENT_LOG_ID => 'id',
                        EventLog::DATE => 'eventDate',
                        EventLog::TRIGGERED_DETAILS => 'driverId',
                        EventLog::EVENT_TEAM => 'entityTeamId',
                    ],
                ],
            ],
            'triggeredBy' => Event::BY_DRIVER
        ],
        [
            'implemented' => true,
            'name' => Event::VEHICLE_TOWING_EVENT,
            'alias' => 'Vehicle being towed',
            'type' => Event::TYPE_USER,
            'entity' => Event::ENTITY_TYPE_TRACKER_HISTORY,
            'scopes' => [
                ScopeType::VEHICLE =>
                    [
                        ScopeType::GENERAL_SCOPE_CATEGORY =>
                            ScopeType::SCOPE_TO_SUBTYPES[ScopeType::GENERAL_SCOPE_CATEGORY][ScopeType::VEHICLE],
                    ]
            ],
            'importance' => Importance::TYPE_NORMAL,
            Event::ADDITIONAL_SETTING => [
                Event::ADDITIONAL_SETTING_IS_TIME_DURATION => true,
                Event::ADDITIONAL_SETTING_HEADER_COLUMNS => [
                    EventLog::EVENT_LOG_ID => 'headers.general.id',
                    EventLog::DATE => 'headers.general.formattedDate',
                    EventLog::IMPORTANCE => 'headers.general.importance',
                    EventLog::TRIGGERED_DETAILS => 'headers.trackerInfo.triggeredDetails',
                    EventLog::EVENT_TEAM => 'headers.general.eventTeam',
                    EventLog::EVENT_SOURCE => 'headers.trackerInfo.eventSource',
                    EventLog::SHORT_DETAILS => 'headers.trackerInfo.shortDetails',
                    EventLog::DEVICE_IMEI => 'headers.trackerInfo.imei',
                    EventLog::ADDRESS => 'headers.trackerInfo.address',
                    EventLog::NTF_LIST => 'headers.general.notificationsList',
                ],
                Event::SETTING_EVENT_LOG => [
                    Event::ADDITIONAL_SETTING_MAPPING_FILTERS => [
                        EventLog::EVENT_ID => 'eventId',
                        EventLog::EVENT_LOG_ID => 'id',
                        EventLog::DATE => 'eventDate',
                        EventLog::EVENT_TEAM => 'entityTeamId',
                        EventLog::TRIGGERED_DETAILS => 'driverId',
                        EventLog::EVENT_SOURCE => 'vehicleId',
                    ],
                ],
            ],
            'triggeredBy' => Event::BY_DRIVER
        ],
        [
            'implemented' => true,
            'name' => Event::TRACKER_JAMMER_STARTED_ALARM,
            'alias' => 'Jammer is started',
            'type' => Event::TYPE_USER,
            'entity' => Event::ENTITY_TYPE_TRACKER_HISTORY,
            'scopes' => [
                ScopeType::VEHICLE =>
                    [
                        ScopeType::GENERAL_SCOPE_CATEGORY =>
                            ScopeType::SCOPE_TO_SUBTYPES[ScopeType::GENERAL_SCOPE_CATEGORY][ScopeType::VEHICLE],
                    ]
            ],
            'importance' => Importance::TYPE_NORMAL,
            Event::ADDITIONAL_SETTING => [
                Event::ADDITIONAL_SETTING_HEADER_COLUMNS => [
                    EventLog::EVENT_LOG_ID => 'headers.general.id',
                    EventLog::DATE => 'headers.general.formattedDate',
                    EventLog::IMPORTANCE => 'headers.general.importance',
                    EventLog::TRIGGERED_DETAILS => 'headers.trackerInfo.triggeredDetails',
                    EventLog::EVENT_TEAM => 'headers.general.eventTeam',
                    EventLog::EVENT_SOURCE => 'headers.trackerInfo.eventSource',
                    EventLog::SHORT_DETAILS => 'headers.trackerInfo.shortDetails',
                    EventLog::DEVICE_IMEI => 'headers.trackerInfo.imei',
                    EventLog::NTF_LIST => 'headers.general.notificationsList',
                ],
                Event::SETTING_EVENT_LOG => [
                    Event::ADDITIONAL_SETTING_MAPPING_FILTERS => [
                        EventLog::EVENT_ID => 'eventId',
                        EventLog::EVENT_LOG_ID => 'id',
                        EventLog::DATE => 'eventDate',
                        EventLog::EVENT_TEAM => 'entityTeamId',
                        EventLog::TRIGGERED_DETAILS => 'driverId',
                        EventLog::EVENT_SOURCE => 'vehicleId',
                    ],
                ],
            ],
            'triggeredBy' => Event::BY_DRIVER
        ],
        [
            'implemented' => true,
            'name' => Event::VEHICLE_DRIVING_WITHOUT_DRIVER,
            'alias' => 'Driving without assigned driver',
            'type' => Event::TYPE_USER,
            'entity' => Event::ENTITY_TYPE_TRACKER_HISTORY,
            'scopes' => [
                ScopeType::VEHICLE =>
                    [
                        ScopeType::GENERAL_SCOPE_CATEGORY =>
                            ScopeType::SCOPE_TO_SUBTYPES[ScopeType::GENERAL_SCOPE_CATEGORY][ScopeType::VEHICLE],
                    ]
            ],
            'importance' => Importance::TYPE_NORMAL,
            Event::ADDITIONAL_SETTING => [
                Event::ADDITIONAL_SETTING_IS_TIME_DURATION => true,
                Event::ADDITIONAL_SETTING_IS_DISTANCE => true,
                Event::ADDITIONAL_SETTING_IS_EXPRESSION_OPERATOR => true,
                Event::ADDITIONAL_SETTING_HEADER_COLUMNS => [
                    EventLog::EVENT_LOG_ID => 'headers.general.id',
                    EventLog::DATE => 'headers.general.formattedDate',
                    EventLog::IMPORTANCE => 'headers.general.importance',
                    EventLog::EVENT_TEAM => 'headers.general.eventTeam',
                    EventLog::EVENT_SOURCE => 'headers.trackerInfo.eventSource',
                    EventLog::SHORT_DETAILS => 'headers.trackerInfo.shortDetails',
                    EventLog::DEVICE_IMEI => 'headers.trackerInfo.imei',
                    EventLog::NTF_LIST => 'headers.general.notificationsList',
                ],
                Event::SETTING_EVENT_LOG => [
                    Event::ADDITIONAL_SETTING_MAPPING_FILTERS => [
                        EventLog::EVENT_ID => 'eventId',
                        EventLog::EVENT_LOG_ID => 'id',
                        EventLog::DATE => 'eventDate',
                        EventLog::EVENT_TEAM => 'entityTeamId',
//                        EventLog::TRIGGERED_DETAILS => 'driverId',
                        EventLog::EVENT_SOURCE => 'vehicleId',
                    ],
                ],
            ],
        ],
        [
            'implemented' => true,
            'name' => Event::DRIVER_ROUTE_UNDEFINED,
            'alias' => 'Driver route undefined',
            'type' => Event::TYPE_USER,
            'entity' => Event::ENTITY_TYPE_VEHICLE,
            'scopes' => [
                ScopeType::VEHICLE =>
                    [
                        ScopeType::GENERAL_SCOPE_CATEGORY =>
                            ScopeType::SCOPE_TO_SUBTYPES[ScopeType::GENERAL_SCOPE_CATEGORY][ScopeType::VEHICLE],
                    ]
            ],
            'importance' => Importance::TYPE_NORMAL,
            'triggeredBy' => Event::BY_DRIVER,
            Event::ADDITIONAL_SETTING => [
                Event::ADDITIONAL_SETTING_HEADER_COLUMNS => [
                    EventLog::EVENT_LOG_ID => 'headers.general.id',
                    EventLog::DATE => 'headers.general.formattedDate',
                    EventLog::IMPORTANCE => 'headers.general.importance',
                    EventLog::EVENT_TEAM => 'headers.general.eventTeam',
                    EventLog::EVENT_SOURCE => 'headers.trackerInfo.eventSource',
                    EventLog::NTF_LIST => 'headers.general.notificationsList',
                ]
            ],
        ],
        [
            'implemented' => true,
            'name' => Event::SERVICE_RECORD_ADDED,
            'alias' => 'Service reminder added',
            'type' => Event::TYPE_USER,
            'entity' => Event::ENTITY_TYPE_SERVICE_RECORD,
            'scopes' => [
                ScopeType::SERVICE_RECORD =>
                    [
                        ScopeType::GENERAL_SCOPE_CATEGORY =>
                            ScopeType::SCOPE_TO_SUBTYPES[ScopeType::GENERAL_SCOPE_CATEGORY][ScopeType::SERVICE_RECORD],
                    ]
            ],
            'importance' => Importance::TYPE_NORMAL,
            Event::ADDITIONAL_SETTING => [
                Event::ADDITIONAL_SETTING_HEADER_COLUMNS => [
                    EventLog::EVENT_LOG_ID => 'headers.general.id',
                    EventLog::DATE => 'headers.general.formattedDate',
                    EventLog::IMPORTANCE => 'headers.general.importance',
                    EventLog::TRIGGERED_DETAILS => 'headers.documentInfo.triggeredDetails',
                    EventLog::EVENT_TEAM => 'headers.general.eventTeam',
                    EventLog::EVENT_SOURCE => 'headers.documentInfo.eventSource',
                    EventLog::VEHICLE_REG_NO => 'headers.documentInfo.vehicleRegNo',
                    EventLog::NTF_LIST => 'headers.general.notificationsList',
                ],
                Event::SETTING_EVENT_LOG => [
                    Event::ADDITIONAL_SETTING_MAPPING_FILTERS => [
                        EventLog::EVENT_ID => 'eventId',
                        EventLog::EVENT_LOG_ID => 'id',
                        EventLog::DATE => 'eventDate',
                        EventLog::TRIGGERED_DETAILS => 'userBy',
                        EventLog::EVENT_TEAM => 'entityTeamId',
                    ],
                ],
            ],
            'triggeredBy' => Event::BY_USER
        ],
        [
            'implemented' => true,
            'name' => Event::SERVICE_REPAIR_ADDED,
            'alias' => 'Repair Added',
            'type' => Event::TYPE_USER,
            'entity' => Event::ENTITY_TYPE_SERVICE_RECORD,
            'scopes' => [
                ScopeType::SERVICE_RECORD =>
                    [
                        ScopeType::GENERAL_SCOPE_CATEGORY =>
                            ScopeType::SCOPE_TO_SUBTYPES[ScopeType::GENERAL_SCOPE_CATEGORY][ScopeType::SERVICE_RECORD],
                    ]
            ],
            'importance' => Importance::TYPE_NORMAL,
            Event::ADDITIONAL_SETTING => [
                Event::ADDITIONAL_SETTING_HEADER_COLUMNS => [
                    EventLog::EVENT_LOG_ID => 'headers.general.id',
                    EventLog::DATE => 'headers.general.formattedDate',
                    EventLog::IMPORTANCE => 'headers.general.importance',
                    EventLog::TRIGGERED_DETAILS => 'headers.documentInfo.triggeredDetails',
                    EventLog::EVENT_TEAM => 'headers.general.eventTeam',
                    EventLog::EVENT_SOURCE => 'headers.documentInfo.eventSource',
                    EventLog::VEHICLE_REG_NO => 'headers.documentInfo.vehicleRegNo',
                    EventLog::NTF_LIST => 'headers.general.notificationsList',
                ],
                Event::SETTING_EVENT_LOG => [
                    Event::ADDITIONAL_SETTING_MAPPING_FILTERS => [
                        EventLog::EVENT_ID => 'eventId',
                        EventLog::EVENT_LOG_ID => 'id',
                        EventLog::DATE => 'eventDate',
                        EventLog::TRIGGERED_DETAILS => 'userBy',
                        EventLog::EVENT_TEAM => 'entityTeamId',
                    ],
                ],
            ],
            'triggeredBy' => Event::BY_USER
        ],
        [
            'implemented' => true,
            'name' => Event::DOCUMENT_RECORD_ADDED,
            'alias' => 'Document added',
            'type' => Event::TYPE_USER,
            'entity' => Event::ENTITY_TYPE_DOCUMENT_RECORD,
            'scopes' => [
                ScopeType::DOCUMENT_RECORD =>
                    [
                        ScopeType::GENERAL_SCOPE_CATEGORY =>
                            ScopeType::SCOPE_TO_SUBTYPES[ScopeType::GENERAL_SCOPE_CATEGORY][ScopeType::DOCUMENT_RECORD],
                    ]
            ],
            'importance' => Importance::TYPE_NORMAL,
            Event::ADDITIONAL_SETTING => [
                Event::ADDITIONAL_SETTING_HEADER_COLUMNS => [
                    EventLog::EVENT_LOG_ID => 'headers.general.id',
                    EventLog::DATE => 'headers.general.formattedDate',
                    EventLog::IMPORTANCE => 'headers.general.importance',
                    EventLog::EVENT_TEAM => 'headers.general.eventTeam',
                    EventLog::EVENT_SOURCE => 'headers.documentRecordInfo.eventSource',
                    EventLog::TITLE => 'headers.documentRecordInfo.title',
                    EventLog::NTF_LIST => 'headers.general.notificationsList',
                ],
                Event::SETTING_EVENT_LOG => [
                    Event::ADDITIONAL_SETTING_MAPPING_FILTERS => [
                        EventLog::EVENT_LOG_ID => 'id',
                        EventLog::DATE => 'eventDate',
                        EventLog::EVENT_TEAM => 'entityTeamId',
                        EventLog::EVENT_SOURCE => 'vehicleId',
                        EventLog::EVENT_ID => 'eventId',
                    ],
                ],
            ],
            'triggeredBy' => Event::BY_USER
        ],
        [
            'implemented' => true,
            'name' => Event::TRACKER_VOLTAGE,
            'alias' => 'Supply Voltage',
            'type' => Event::TYPE_USER,
            'entity' => Event::ENTITY_TYPE_TRACKER_HISTORY,
            'scopes' => [
                ScopeType::VEHICLE =>
                    [
                        ScopeType::GENERAL_SCOPE_CATEGORY =>
                            ScopeType::SCOPE_TO_SUBTYPES[ScopeType::GENERAL_SCOPE_CATEGORY][ScopeType::TRACKER_HISTORY],
                    ],
                ScopeType::AREA =>
                    [
                        ScopeType::ADDITIONAL_SCOPE_CATEGORY =>
                            ScopeType::SCOPE_TO_SUBTYPES[ScopeType::ADDITIONAL_SCOPE_CATEGORY][ScopeType::AREA]
                    ]
            ],
            'importance' => Importance::TYPE_NORMAL,
            Event::ADDITIONAL_SETTING => [
                Event::ADDITIONAL_SETTING_IS_DEVICE_VOLTAGE => true,
                Event::ADDITIONAL_SETTING_IS_TIME_DURATION => true,
                Event::ADDITIONAL_SETTING_IS_DISTANCE => true,
                Event::ADDITIONAL_SETTING_IS_EXPRESSION_OPERATOR => true,
                Event::ADDITIONAL_SETTING_IS_AREA_TRIGGER_TYPE => true,
                Event::ADDITIONAL_SETTING_HEADER_COLUMNS => [
                    EventLog::EVENT_LOG_ID => 'headers.general.id',
                    EventLog::DATE => 'headers.general.formattedDate',
                    EventLog::IMPORTANCE => 'headers.general.importance',
                    EventLog::TRIGGERED_DETAILS => 'headers.trackerInfo.triggeredDetails',
                    EventLog::EVENT_TEAM => 'headers.general.eventTeam',
                    EventLog::EVENT_SOURCE => 'headers.trackerInfo.eventSource',
                    EventLog::DEVICE_VOLTAGE => 'headers.trackerInfo.deviceVoltage',
                    EventLog::LIMIT => 'headers.general.limit',
                    EventLog::NTF_LIST => 'headers.general.notificationsList',
                ],
                Event::SETTING_EVENT_LOG => [
                    Event::ADDITIONAL_SETTING_MAPPING_FILTERS => [
                        EventLog::EVENT_ID => 'eventId',
                        EventLog::EVENT_LOG_ID => 'id',
                        EventLog::DATE => 'eventDate',
                        EventLog::EVENT_TEAM => 'entityTeamId',
                        EventLog::TRIGGERED_DETAILS => 'driverId',
                        EventLog::EVENT_SOURCE => 'vehicleId',
                    ],
                ],
            ],
            'triggeredBy' => Event::BY_DRIVER
        ],
        [
            'implemented' => true,
            'name' => Event::PANIC_BUTTON,
            'alias' => 'SOS button',
            'type' => Event::TYPE_USER,
            'entity' => Event::ENTITY_TYPE_TRACKER_HISTORY,
            'scopes' => [
                ScopeType::TRACKER_HISTORY =>
                    [
                        ScopeType::GENERAL_SCOPE_CATEGORY =>
                            ScopeType::SCOPE_TO_SUBTYPES[ScopeType::GENERAL_SCOPE_CATEGORY][ScopeType::PANIC_BUTTON],
                    ]
            ],
            'importance' => Importance::TYPE_NORMAL,
            Event::ADDITIONAL_SETTING => [
                Event::ADDITIONAL_SETTING_HEADER_COLUMNS => [
                    EventLog::EVENT_LOG_ID => 'headers.general.id',
                    EventLog::DATE => 'headers.general.formattedDate',
                    EventLog::IMPORTANCE => 'headers.general.importance',
                    EventLog::TRIGGERED_DETAILS => 'headers.trackerInfo.triggeredDetails',
                    EventLog::EVENT_TEAM => 'headers.general.eventTeam',
                    EventLog::EVENT_SOURCE => 'headers.trackerInfo.eventSource',
                    EventLog::SHORT_DETAILS => 'headers.trackerInfo.shortDetails',
                    EventLog::DEVICE_IMEI => 'headers.trackerInfo.imei',
                    EventLog::ADDRESS => 'headers.trackerInfo.address',
                    EventLog::NTF_LIST => 'headers.general.notificationsList',
                ],
                Event::SETTING_EVENT_LOG => [
                    Event::ADDITIONAL_SETTING_MAPPING_FILTERS => [
                        EventLog::EVENT_ID => 'eventId',
                        EventLog::EVENT_LOG_ID => 'id',
                        EventLog::DATE => 'eventDate',
                        EventLog::EVENT_TEAM => 'entityTeamId',
                        EventLog::TRIGGERED_DETAILS => 'driverId',
                        EventLog::EVENT_SOURCE => 'vehicleId',
                    ],
                ],
            ],
            'triggeredBy' => Event::BY_DRIVER
        ],
        [
            'implemented' => true,
            'name' => Event::ODOMETER_CORRECTED,
            'alias' => 'Vehicle odometer corrected',
            'type' => Event::TYPE_USER,
            'entity' => Event::ENTITY_TYPE_VEHICLE_ODOMETER,
            'scopes' => [
                ScopeType::VEHICLE =>
                    [
                        ScopeType::GENERAL_SCOPE_CATEGORY =>
                            ScopeType::SCOPE_TO_SUBTYPES[ScopeType::GENERAL_SCOPE_CATEGORY][ScopeType::VEHICLE],
                    ]
            ],
            'importance' => Importance::TYPE_NORMAL,
            Event::ADDITIONAL_SETTING => [
                Event::ADDITIONAL_SETTING_HEADER_COLUMNS => [
                    EventLog::EVENT_LOG_ID => 'headers.general.id',
                    EventLog::DATE => 'headers.general.formattedDate',
                    EventLog::IMPORTANCE => 'headers.general.importance',
                    EventLog::EVENT_TEAM => 'headers.general.eventTeam',
                    EventLog::EVENT_SOURCE => 'headers.documentInfo.eventSource',
                    EventLog::OLD_VALUE => 'headers.general.oldValue',
                    EventLog::NEW_VALUE => 'headers.general.newValue',
                    EventLog::NTF_LIST => 'headers.general.notificationsList',
                ],
                Event::SETTING_EVENT_LOG => [
                    Event::ADDITIONAL_SETTING_MAPPING_FILTERS => [
                        EventLog::EVENT_ID => 'eventId',
                        EventLog::EVENT_LOG_ID => 'id',
                        EventLog::DATE => 'eventDate',
                        EventLog::EVENT_TEAM => 'entityTeamId',
                    ],
                ],
            ],
            'triggeredBy' => Event::BY_USER
        ],
        [
            'implemented' => true,
            'name' => Event::DIGITAL_FORM_WITH_FAIL,
            'alias' => 'Failed inspection',
            'type' => Event::TYPE_USER,
            'entity' => Event::ENTITY_TYPE_DIGITAL_FORM_ANSWER,
            'scopes' => [
                ScopeType::VEHICLE =>
                    [
                        ScopeType::GENERAL_SCOPE_CATEGORY =>
                            ScopeType::SCOPE_TO_SUBTYPES[ScopeType::GENERAL_SCOPE_CATEGORY][ScopeType::VEHICLE],
                    ]
            ],
            'importance' => Importance::TYPE_NORMAL,
            Event::ADDITIONAL_SETTING => [
                Event::ADDITIONAL_SETTING_HEADER_COLUMNS => [
                    EventLog::EVENT_LOG_ID => 'headers.general.id',
                    EventLog::DATE => 'headers.general.formattedDate',
                    EventLog::IMPORTANCE => 'headers.general.importance',
                    EventLog::EVENT_TEAM => 'headers.general.eventTeam',
                    EventLog::EVENT_SOURCE => 'headers.digitalForm.eventSource',
                    EventLog::VEHICLE_REG_NO => 'headers.digitalForm.vehicleRegNo',
                    EventLog::USER => 'headers.digitalForm.user',
                    EventLog::NTF_LIST => 'headers.general.notificationsList',
                ],
                Event::SETTING_EVENT_LOG => [
                    Event::ADDITIONAL_SETTING_MAPPING_FILTERS => [
                        EventLog::EVENT_ID => 'eventId',
                        EventLog::EVENT_LOG_ID => 'id',
                        EventLog::DATE => 'eventDate',
                        EventLog::EVENT_TEAM => 'entityTeamId',
                    ],
                ],
            ],
            'triggeredBy' => Event::BY_USER
        ],
        [
            'implemented' => true,
            'name' => Event::DIGITAL_FORM_IS_NOT_COMPLETED,
            'alias' => 'Digital form is not completed',
            'type' => Event::TYPE_USER,
            'entity' => Event::ENTITY_TYPE_ROUTE,
            'scopes' => [
                ScopeType::VEHICLE =>
                    [
                        ScopeType::GENERAL_SCOPE_CATEGORY =>
                            ScopeType::SCOPE_TO_SUBTYPES[ScopeType::GENERAL_SCOPE_CATEGORY][ScopeType::VEHICLE],
                    ]
            ],
            'importance' => Importance::TYPE_NORMAL,
            Event::ADDITIONAL_SETTING => [
                Event::ADDITIONAL_SETTING_IS_DRIVER_TO_RECIPIENT => false,
                Event::ADDITIONAL_SETTING_HEADER_COLUMNS => [
                    EventLog::EVENT_LOG_ID => 'headers.general.id',
                    EventLog::DATE => 'headers.general.formattedDate',
                    EventLog::IMPORTANCE => 'headers.general.importance',
                    EventLog::EVENT_TEAM => 'headers.general.eventTeam',
                    EventLog::FORM => 'headers.digitalForm.title',
                    EventLog::EVENT_SOURCE => 'headers.digitalForm.vehicleRegNo',
                    EventLog::TRIGGERED_DETAILS => 'headers.digitalForm.triggeredDetails',
                    EventLog::NTF_LIST => 'headers.general.notificationsList',
                ],
                Event::SETTING_EVENT_LOG => [
                    Event::ADDITIONAL_SETTING_MAPPING_FILTERS => [
                        EventLog::EVENT_ID => 'eventId',
                        EventLog::EVENT_LOG_ID => 'id',
                        EventLog::DATE => 'eventDate',
                        EventLog::EVENT_TEAM => 'entityTeamId',
                        EventLog::TRIGGERED_DETAILS => 'driverId',
                    ],
                ],
            ],
            'triggeredBy' => Event::BY_USER
        ],
        [
            'implemented' => true,
            'name' => Event::DIGITAL_FORM_IS_NOT_COMPLETED,
            'alias' => 'Digital form is not completed',
            'type' => Event::TYPE_SYSTEM,
            'entity' => Event::ENTITY_TYPE_ROUTE,
            'scopes' => [
                ScopeType::VEHICLE =>
                    [
                        ScopeType::GENERAL_SCOPE_CATEGORY =>
                            ScopeType::SCOPE_TO_SUBTYPES[ScopeType::GENERAL_SCOPE_CATEGORY][ScopeType::VEHICLE],
                    ]
            ],
            'importance' => Importance::TYPE_NORMAL,
            Event::ADDITIONAL_SETTING => [
                Event::ADDITIONAL_SETTING_IS_DRIVER_TO_RECIPIENT => true,
                Event::ADDITIONAL_SETTING_HEADER_COLUMNS => [
                    EventLog::EVENT_LOG_ID => 'headers.general.id',
                    EventLog::DATE => 'headers.general.formattedDate',
                    EventLog::IMPORTANCE => 'headers.general.importance',
                    EventLog::EVENT_TEAM => 'headers.general.eventTeam',
                    EventLog::EVENT_SOURCE => 'headers.digitalForm.formNotCompleted.eventSource',
                    EventLog::TRIGGERED_DETAILS => 'headers.digitalForm.triggeredDetails',
                    EventLog::NTF_LIST => 'headers.general.notificationsList',
                ],
                Event::SETTING_EVENT_LOG => [
                    Event::ADDITIONAL_SETTING_MAPPING_FILTERS => [
                        EventLog::EVENT_ID => 'eventId',
                        EventLog::EVENT_LOG_ID => 'id',
                        EventLog::DATE => 'eventDate',
                        EventLog::EVENT_TEAM => 'entityTeamId',
                        EventLog::TRIGGERED_DETAILS => 'driverId',
                    ],
                ],
            ],
            'triggeredBy' => Event::BY_USER
        ],
        [
            'implemented' => true,
            'name' => Event::SENSOR_TEMPERATURE,
            'alias' => 'Sensor temperature',
            'type' => Event::TYPE_USER,
            'entity' => Event::ENTITY_TYPE_TRACKER_HISTORY_SENSOR,
            'scopes' => [
                ScopeType::VEHICLE =>
                    [
                        ScopeType::GENERAL_SCOPE_CATEGORY => [
                            ScopeType::SUBTYPE_ANY,
                            ScopeType::SUBTYPE_VEHICLE,
                            ScopeType::SUBTYPE_DEPOT,
                            ScopeType::SUBTYPE_GROUP,
                            ScopeType::SUBTYPE_SENSOR,
                        ],
                    ]
            ],
            'importance' => Importance::TYPE_NORMAL,
            Event::ADDITIONAL_SETTING => [
                Event::ADDITIONAL_SETTING_IS_SENSOR_TEMPERATURE => true,
                Event::ADDITIONAL_SETTING_HEADER_COLUMNS => [
                    EventLog::EVENT_LOG_ID => 'headers.general.id',
                    EventLog::DATE => 'headers.general.formattedDate',
                    EventLog::IMPORTANCE => 'headers.general.importance',
                    EventLog::TRIGGERED_DETAILS => 'headers.sensor.triggeredDetails',
                    EventLog::EVENT_TEAM => 'headers.general.eventTeam',
                    EventLog::EVENT_SOURCE => 'headers.trackerInfo.eventSource',
                    EventLog::SENSOR_TEMPERATURE => 'headers.sensor.temperature',
                    EventLog::LIMIT => 'headers.general.limit',
                    EventLog::NTF_LIST => 'headers.general.notificationsList',
                ],
                Event::SETTING_EVENT_LOG => [
                    Event::ADDITIONAL_SETTING_MAPPING_FILTERS => [
                        EventLog::EVENT_ID => 'eventId',
                        EventLog::EVENT_LOG_ID => 'id',
                        EventLog::DATE => 'eventDate',
                        EventLog::EVENT_TEAM => 'entityTeamId',
                        EventLog::TRIGGERED_DETAILS => 'entityId',
                    ],
                ],
            ],
            'triggeredBy' => Event::BY_SENSOR
        ],
        [
            'implemented' => true,
            'name' => Event::SENSOR_HUMIDITY,
            'alias' => 'Sensor humidity',
            'type' => Event::TYPE_USER,
            'entity' => Event::ENTITY_TYPE_TRACKER_HISTORY_SENSOR,
            'scopes' => [
                ScopeType::VEHICLE =>
                    [
                        ScopeType::GENERAL_SCOPE_CATEGORY => [
                            ScopeType::SUBTYPE_ANY,
                            ScopeType::SUBTYPE_VEHICLE,
                            ScopeType::SUBTYPE_DEPOT,
                            ScopeType::SUBTYPE_GROUP,
                            ScopeType::SUBTYPE_SENSOR,
                        ],
                    ]
            ],
            'importance' => Importance::TYPE_NORMAL,
            Event::ADDITIONAL_SETTING => [
                Event::ADDITIONAL_SETTING_IS_SENSOR_HUMIDITY => true,
                Event::ADDITIONAL_SETTING_HEADER_COLUMNS => [
                    EventLog::EVENT_LOG_ID => 'headers.general.id',
                    EventLog::DATE => 'headers.general.formattedDate',
                    EventLog::IMPORTANCE => 'headers.general.importance',
                    EventLog::TRIGGERED_DETAILS => 'headers.sensor.triggeredDetails',
                    EventLog::EVENT_TEAM => 'headers.general.eventTeam',
                    EventLog::EVENT_SOURCE => 'headers.trackerInfo.eventSource',
                    EventLog::SENSOR_HUMIDITY => 'headers.sensor.humidity',
                    EventLog::LIMIT => 'headers.general.limit',
                    EventLog::NTF_LIST => 'headers.general.notificationsList',
                ],
                Event::SETTING_EVENT_LOG => [
                    Event::ADDITIONAL_SETTING_MAPPING_FILTERS => [
                        EventLog::EVENT_ID => 'eventId',
                        EventLog::EVENT_LOG_ID => 'id',
                        EventLog::DATE => 'eventDate',
                        EventLog::EVENT_TEAM => 'entityTeamId',
                        EventLog::TRIGGERED_DETAILS => 'entityId',
                    ],
                ],
            ],
            'triggeredBy' => Event::BY_SENSOR
        ],
        [
            'implemented' => true,
            'name' => Event::SENSOR_LIGHT,
            'alias' => 'Sensor light',
            'type' => Event::TYPE_USER,
            'entity' => Event::ENTITY_TYPE_TRACKER_HISTORY_SENSOR,
            'scopes' => [
                ScopeType::VEHICLE =>
                    [
                        ScopeType::GENERAL_SCOPE_CATEGORY => [
                            ScopeType::SUBTYPE_ANY,
                            ScopeType::SUBTYPE_VEHICLE,
                            ScopeType::SUBTYPE_DEPOT,
                            ScopeType::SUBTYPE_GROUP,
                            ScopeType::SUBTYPE_SENSOR,
                        ],
                    ]
            ],
            'importance' => Importance::TYPE_NORMAL,
            Event::ADDITIONAL_SETTING => [
                Event::ADDITIONAL_SETTING_IS_SENSOR_LIGHT => true,
                Event::ADDITIONAL_SETTING_HEADER_COLUMNS => [
                    EventLog::EVENT_LOG_ID => 'headers.general.id',
                    EventLog::DATE => 'headers.general.formattedDate',
                    EventLog::IMPORTANCE => 'headers.general.importance',
                    EventLog::TRIGGERED_DETAILS => 'headers.sensor.triggeredDetails',
                    EventLog::EVENT_TEAM => 'headers.general.eventTeam',
                    EventLog::EVENT_SOURCE => 'headers.trackerInfo.eventSource',
                    EventLog::SENSOR_LIGHT => 'headers.sensor.light',
                    EventLog::NTF_LIST => 'headers.general.notificationsList',
                ],
                Event::SETTING_EVENT_LOG => [
                    Event::ADDITIONAL_SETTING_MAPPING_FILTERS => [
                        EventLog::EVENT_ID => 'eventId',
                        EventLog::EVENT_LOG_ID => 'id',
                        EventLog::DATE => 'eventDate',
                        EventLog::EVENT_TEAM => 'entityTeamId',
                    ],
                ],
            ],
            'triggeredBy' => Event::BY_SENSOR
        ],
        [
            'implemented' => true,
            'name' => Event::SENSOR_BATTERY_LEVEL,
            'alias' => 'Sensor battery level',
            'type' => Event::TYPE_USER,
            'entity' => Event::ENTITY_TYPE_TRACKER_HISTORY_SENSOR,
            'scopes' => [
                ScopeType::VEHICLE =>
                    [
                        ScopeType::GENERAL_SCOPE_CATEGORY => [
                            ScopeType::SUBTYPE_ANY,
                            ScopeType::SUBTYPE_VEHICLE,
                            ScopeType::SUBTYPE_DEPOT,
                            ScopeType::SUBTYPE_GROUP,
                            ScopeType::SUBTYPE_SENSOR,
                        ],
                    ]
            ],
            'importance' => Importance::TYPE_NORMAL,
            Event::ADDITIONAL_SETTING => [
                Event::ADDITIONAL_SETTING_IS_SENSOR_BATTERY_LEVEL => true,
                Event::ADDITIONAL_SETTING_HEADER_COLUMNS => [
                    EventLog::EVENT_LOG_ID => 'headers.general.id',
                    EventLog::DATE => 'headers.general.formattedDate',
                    EventLog::IMPORTANCE => 'headers.general.importance',
                    EventLog::TRIGGERED_DETAILS => 'headers.sensor.triggeredDetails',
                    EventLog::EVENT_TEAM => 'headers.general.eventTeam',
                    EventLog::EVENT_SOURCE => 'headers.trackerInfo.eventSource',
                    EventLog::SENSOR_BATTERY_LEVEL => 'headers.sensor.batteryLevel',
                    EventLog::LIMIT => 'headers.general.limit',
                    EventLog::NTF_LIST => 'headers.general.notificationsList',
                ],
                Event::SETTING_EVENT_LOG => [
                    Event::ADDITIONAL_SETTING_MAPPING_FILTERS => [
                        EventLog::EVENT_ID => 'eventId',
                        EventLog::EVENT_LOG_ID => 'id',
                        EventLog::DATE => 'eventDate',
                        EventLog::EVENT_TEAM => 'entityTeamId',
                    ],
                ],
            ],
            'triggeredBy' => Event::BY_SENSOR
        ],
        [
            'implemented' => true,
            'name' => Event::SENSOR_STATUS,
            'alias' => 'Sensor status',
            'type' => Event::TYPE_USER,
            'entity' => Event::ENTITY_TYPE_TRACKER_HISTORY_SENSOR,
            'scopes' => [
                ScopeType::VEHICLE =>
                    [
                        ScopeType::GENERAL_SCOPE_CATEGORY => [
                            ScopeType::SUBTYPE_ANY,
                            ScopeType::SUBTYPE_VEHICLE,
                            ScopeType::SUBTYPE_DEPOT,
                            ScopeType::SUBTYPE_GROUP,
                            ScopeType::SUBTYPE_SENSOR,
                        ],
                    ]
            ],
            'importance' => Importance::TYPE_NORMAL,
            Event::ADDITIONAL_SETTING => [
                Event::ADDITIONAL_SETTING_IS_SENSOR_STATUS => true,
                Event::ADDITIONAL_SETTING_HEADER_COLUMNS => [
                    EventLog::EVENT_LOG_ID => 'headers.general.id',
                    EventLog::DATE => 'headers.general.formattedDate',
                    EventLog::IMPORTANCE => 'headers.general.importance',
                    EventLog::TRIGGERED_DETAILS => 'headers.sensor.triggeredDetails',
                    EventLog::EVENT_TEAM => 'headers.general.eventTeam',
                    EventLog::EVENT_SOURCE => 'headers.trackerInfo.eventSource',
                    EventLog::SENSOR_STATUS => 'headers.trackerInfo.status',
                    EventLog::NTF_LIST => 'headers.general.notificationsList',
                ],
                Event::SETTING_EVENT_LOG => [
                    Event::ADDITIONAL_SETTING_MAPPING_FILTERS => [
                        EventLog::EVENT_ID => 'eventId',
                        EventLog::EVENT_LOG_ID => 'id',
                        EventLog::DATE => 'eventDate',
                        EventLog::EVENT_TEAM => 'entityTeamId',
                    ],
                ],
            ],
            'triggeredBy' => Event::BY_SENSOR
        ],
        [
            'implemented' => true,
            'name' => Event::DRIVER_DOCUMENT_EXPIRE_SOON,
            'alias' => 'Document expiring soon',
            'type' => Event::TYPE_USER,
            'entity' => Event::ENTITY_TYPE_DOCUMENT_RECORD,
            'scopes' => [
                ScopeType::DOCUMENT_RECORD =>
                    [
                        ScopeType::GENERAL_SCOPE_CATEGORY =>
                            ScopeType::SCOPE_TO_SUBTYPES[ScopeType::GENERAL_SCOPE_CATEGORY][ScopeType::DOCUMENT_RECORD],
                    ]
            ],
            Event::ADDITIONAL_SETTING => [
                Event::ADDITIONAL_SETTING_HEADER_COLUMNS => [
                    EventLog::EVENT_LOG_ID => 'headers.general.id',
                    EventLog::DATE => 'headers.general.formattedDate',
                    EventLog::IMPORTANCE => 'headers.general.importance',
                    EventLog::EVENT_SOURCE => 'headers.documentRecordInfo.eventSource',
                    EventLog::TITLE => 'headers.documentRecordInfo.title',
                    EventLog::EXPIRED_DATE => 'headers.documentRecordInfo.expiredDate',
                    EventLog::NTF_LIST => 'headers.general.notificationsList',
                ],
                Event::SETTING_EVENT_LOG => [
                    Event::ADDITIONAL_SETTING_MAPPING_FILTERS => [
                        EventLog::EVENT_LOG_ID => 'id',
                        EventLog::DATE => 'eventDate',
                        EventLog::EVENT_SOURCE => 'driverId',
                        EventLog::EVENT_ID => 'eventId',
                        EventLog::EVENT_TEAM => 'entityTeamId'
                    ],
                ],
            ],
            'importance' => Importance::TYPE_NORMAL,
        ],
        [
            'implemented' => true,
            'name' => Event::DRIVER_DOCUMENT_EXPIRED,
            'alias' => 'Document expired',
            'type' => Event::TYPE_USER,
            'entity' => Event::ENTITY_TYPE_DOCUMENT_RECORD,
            'scopes' => [
                ScopeType::DOCUMENT_RECORD =>
                    [
                        ScopeType::GENERAL_SCOPE_CATEGORY =>
                            ScopeType::SCOPE_TO_SUBTYPES[ScopeType::GENERAL_SCOPE_CATEGORY][ScopeType::DOCUMENT_RECORD],
                    ]
            ],
            Event::ADDITIONAL_SETTING => [
                Event::ADDITIONAL_SETTING_HEADER_COLUMNS => [
                    EventLog::EVENT_LOG_ID => 'headers.general.id',
                    EventLog::DATE => 'headers.general.formattedDate',
                    EventLog::IMPORTANCE => 'headers.general.importance',
                    EventLog::EVENT_SOURCE => 'headers.documentInfo.triggeredDetails',
                    EventLog::TITLE => 'headers.documentRecordInfo.title',
                    EventLog::NTF_LIST => 'headers.general.notificationsList',
                ],
                Event::SETTING_EVENT_LOG => [
                    Event::ADDITIONAL_SETTING_MAPPING_FILTERS => [
                        EventLog::EVENT_LOG_ID => 'id',
                        EventLog::DATE => 'eventDate',
                        EventLog::EVENT_SOURCE => 'driverId',
                        EventLog::EVENT_ID => 'eventId',
                        EventLog::EVENT_TEAM => 'entityTeamId'
                    ],
                ],
            ],
            'importance' => Importance::TYPE_NORMAL,
        ],
        [
            'implemented' => true,
            'name' => Event::DRIVER_DOCUMENT_DELETED,
            'alias' => 'Document deleted',
            'type' => Event::TYPE_USER,
            'entity' => Event::ENTITY_TYPE_DOCUMENT,
            'scopes' => [
                ScopeType::DOCUMENT =>
                    [
                        ScopeType::GENERAL_SCOPE_CATEGORY =>
                            ScopeType::SCOPE_TO_SUBTYPES[ScopeType::GENERAL_SCOPE_CATEGORY][ScopeType::DOCUMENT],
                    ]
            ],
            'importance' => Importance::TYPE_NORMAL,
            'triggeredBy' => Event::BY_USER,
            Event::ADDITIONAL_SETTING => [
                Event::ADDITIONAL_SETTING_HEADER_COLUMNS => [
                    EventLog::EVENT_LOG_ID => 'headers.general.id',
                    EventLog::DATE => 'headers.general.formattedDate',
                    EventLog::IMPORTANCE => 'headers.general.importance',
                    EventLog::TRIGGERED_DETAILS => 'headers.documentInfo.triggeredDetails',
                    EventLog::EVENT_SOURCE => 'headers.documentInfo.eventSource',
                    EventLog::NTF_LIST => 'headers.general.notificationsList',
                ],
                Event::SETTING_EVENT_LOG => [
                    Event::ADDITIONAL_SETTING_MAPPING_FILTERS => [
                        EventLog::EVENT_LOG_ID => 'id',
                        EventLog::DATE => 'eventDate',
                        EventLog::TRIGGERED_DETAILS => 'userBy',
                        EventLog::EVENT_TEAM => 'entityTeamId',
//                        EventLog::EVENT_SOURCE => 'entityId',
                        EventLog::EVENT_ID => 'eventId',
                    ],
                ],
            ],
        ],
        [
            'implemented' => true,
            'name' => Event::DRIVER_DOCUMENT_RECORD_ADDED,
            'alias' => 'Document added',
            'type' => Event::TYPE_USER,
            'entity' => Event::ENTITY_TYPE_DOCUMENT_RECORD,
            'scopes' => [
                ScopeType::DOCUMENT_RECORD =>
                    [
                        ScopeType::GENERAL_SCOPE_CATEGORY =>
                            ScopeType::SCOPE_TO_SUBTYPES[ScopeType::GENERAL_SCOPE_CATEGORY][ScopeType::DOCUMENT_RECORD],
                    ]
            ],
            'importance' => Importance::TYPE_NORMAL,
            Event::ADDITIONAL_SETTING => [
                Event::ADDITIONAL_SETTING_HEADER_COLUMNS => [
                    EventLog::EVENT_LOG_ID => 'headers.general.id',
                    EventLog::DATE => 'headers.general.formattedDate',
                    EventLog::IMPORTANCE => 'headers.general.importance',
                    EventLog::EVENT_TEAM => 'headers.general.eventTeam',
                    EventLog::EVENT_SOURCE => 'headers.documentRecordInfo.eventSource',
                    EventLog::TITLE => 'headers.documentRecordInfo.title',
                    EventLog::NTF_LIST => 'headers.general.notificationsList',
                ],
                Event::SETTING_EVENT_LOG => [
                    Event::ADDITIONAL_SETTING_MAPPING_FILTERS => [
                        EventLog::EVENT_LOG_ID => 'id',
                        EventLog::DATE => 'eventDate',
                        EventLog::EVENT_TEAM => 'entityTeamId',
                        EventLog::EVENT_SOURCE => 'driverId',
                        EventLog::EVENT_ID => 'eventId',
                    ],
                ],
            ],
            'triggeredBy' => Event::BY_USER
        ],
        [
            'implemented' => true,
            'name' => Event::SENSOR_IO_STATUS,
            'alias' => 'Digital I/O',
            'type' => Event::TYPE_USER,
            'entity' => Event::ENTITY_TYPE_TRACKER_HISTORY_IO,
            'scopes' => [
                ScopeType::VEHICLE =>
                    [
                        ScopeType::GENERAL_SCOPE_CATEGORY => [
                            ScopeType::SUBTYPE_ANY,
                            ScopeType::SUBTYPE_VEHICLE,
                            ScopeType::SUBTYPE_DEPOT,
                            ScopeType::SUBTYPE_GROUP,
                            ScopeType::SUBTYPE_SENSOR,
                        ],
                    ]
            ],
            'importance' => Importance::TYPE_NORMAL,
            Event::ADDITIONAL_SETTING => [
                Event::ADDITIONAL_SETTING_IS_SENSOR_IO_STATUS => true,
                Event::ADDITIONAL_SETTING_IS_SENSOR_IO_TYPE => true,
                Event::ADDITIONAL_SETTING_HEADER_COLUMNS => [
                    EventLog::EVENT_LOG_ID => 'headers.general.id',
                    EventLog::DATE => 'headers.general.formattedDate',
                    EventLog::IMPORTANCE => 'headers.general.importance',
                    EventLog::TRIGGERED_DETAILS => 'headers.trackerInfo.triggeredDetails',
                    EventLog::EVENT_TEAM => 'headers.general.eventTeam',
                    EventLog::EVENT_SOURCE => 'headers.trackerInfo.eventSource',
                    EventLog::SENSOR_STATUS => 'headers.trackerInfo.status',
                    EventLog::SENSOR_IO_TYPE => 'headers.trackerInfo.sensorIOTypeId',
                    EventLog::SHORT_DETAILS => 'headers.trackerInfo.shortDetails',
                    EventLog::DEVICE_IMEI => 'headers.trackerInfo.imei',
                    EventLog::NTF_LIST => 'headers.general.notificationsList',
                ],
                Event::SETTING_EVENT_LOG => [
                    Event::ADDITIONAL_SETTING_MAPPING_FILTERS => [
                        EventLog::EVENT_ID => 'eventId',
                        EventLog::EVENT_LOG_ID => 'id',
                        EventLog::DATE => 'eventDate',
                        EventLog::EVENT_TEAM => 'entityTeamId',
                        EventLog::TRIGGERED_DETAILS => 'driverId',
                    ],
                ],
            ],
            'triggeredBy' => Event::BY_DRIVER
        ],
        [
            'implemented' => true,
            'name' => Event::ASSET_DOCUMENT_EXPIRE_SOON,
            'alias' => 'Asset document expire soon',
            'type' => Event::TYPE_USER,
            'entity' => Event::ENTITY_TYPE_ASSET,
            'scopes' => [
                ScopeType::VEHICLE =>
                    [
                        ScopeType::GENERAL_SCOPE_CATEGORY =>
                            ScopeType::SCOPE_TO_SUBTYPES[ScopeType::GENERAL_SCOPE_CATEGORY][ScopeType::TRACKER_HISTORY],
                    ]
            ],
            'importance' => Importance::TYPE_NORMAL,
            Event::ADDITIONAL_SETTING => [
                Event::ADDITIONAL_SETTING_HEADER_COLUMNS => [
                    EventLog::EVENT_LOG_ID => 'headers.general.id',
                    EventLog::DATE => 'headers.general.formattedDate',
                    EventLog::IMPORTANCE => 'headers.general.importance',
                    EventLog::TRIGGERED_DETAILS => 'headers.trackerInfo.triggeredDetails',
                    EventLog::EVENT_TEAM => 'headers.general.eventTeam',
                    EventLog::EVENT_SOURCE => 'headers.trackerInfo.eventSource',
                    EventLog::NTF_LIST => 'headers.general.notificationsList',
                ]
            ],
            'triggeredBy' => Event::BY_DRIVER
        ],
        [
            'implemented' => true,
            'name' => Event::ASSET_DOCUMENT_EXPIRED,
            'alias' => 'Asset document expired',
            'type' => Event::TYPE_USER,
            'entity' => Event::ENTITY_TYPE_ASSET,
            'scopes' => [
                ScopeType::VEHICLE =>
                    [
                        ScopeType::GENERAL_SCOPE_CATEGORY =>
                            ScopeType::SCOPE_TO_SUBTYPES[ScopeType::GENERAL_SCOPE_CATEGORY][ScopeType::TRACKER_HISTORY],
                    ]
            ],
            'importance' => Importance::TYPE_NORMAL,
            Event::ADDITIONAL_SETTING => [
                Event::ADDITIONAL_SETTING_HEADER_COLUMNS => [
                    EventLog::EVENT_LOG_ID => 'headers.general.id',
                    EventLog::DATE => 'headers.general.formattedDate',
                    EventLog::IMPORTANCE => 'headers.general.importance',
                    EventLog::TRIGGERED_DETAILS => 'headers.trackerInfo.triggeredDetails',
                    EventLog::EVENT_TEAM => 'headers.general.eventTeam',
                    EventLog::EVENT_SOURCE => 'headers.trackerInfo.eventSource',
                    EventLog::NTF_LIST => 'headers.general.notificationsList',
                ]
            ],
            'triggeredBy' => Event::BY_DRIVER
        ],
        [
            'implemented' => true,
            'name' => Event::ASSET_DOCUMENT_DELETED,
            'alias' => 'Asset document deleted',
            'type' => Event::TYPE_USER,
            'entity' => Event::ENTITY_TYPE_ASSET,
            'scopes' => [
                ScopeType::VEHICLE =>
                    [
                        ScopeType::GENERAL_SCOPE_CATEGORY =>
                            ScopeType::SCOPE_TO_SUBTYPES[ScopeType::GENERAL_SCOPE_CATEGORY][ScopeType::TRACKER_HISTORY],
                    ]
            ],
            'importance' => Importance::TYPE_NORMAL,
            Event::ADDITIONAL_SETTING => [
                Event::ADDITIONAL_SETTING_HEADER_COLUMNS => [
                    EventLog::EVENT_LOG_ID => 'headers.general.id',
                    EventLog::DATE => 'headers.general.formattedDate',
                    EventLog::IMPORTANCE => 'headers.general.importance',
                    EventLog::TRIGGERED_DETAILS => 'headers.trackerInfo.triggeredDetails',
                    EventLog::EVENT_TEAM => 'headers.general.eventTeam',
                    EventLog::EVENT_SOURCE => 'headers.trackerInfo.eventSource',
                    EventLog::NTF_LIST => 'headers.general.notificationsList',
                ]
            ],
            'triggeredBy' => Event::BY_DRIVER
        ],
        [
            'implemented' => true,
            'name' => Event::ASSET_DOCUMENT_RECORD_ADDED,
            'alias' => 'Asset document record added',
            'type' => Event::TYPE_USER,
            'entity' => Event::ENTITY_TYPE_ASSET,
            'scopes' => [
                ScopeType::VEHICLE =>
                    [
                        ScopeType::GENERAL_SCOPE_CATEGORY =>
                            ScopeType::SCOPE_TO_SUBTYPES[ScopeType::GENERAL_SCOPE_CATEGORY][ScopeType::TRACKER_HISTORY],
                    ]
            ],
            'importance' => Importance::TYPE_NORMAL,
            Event::ADDITIONAL_SETTING => [
                Event::ADDITIONAL_SETTING_HEADER_COLUMNS => [
                    EventLog::EVENT_LOG_ID => 'headers.general.id',
                    EventLog::DATE => 'headers.general.formattedDate',
                    EventLog::IMPORTANCE => 'headers.general.importance',
                    EventLog::TRIGGERED_DETAILS => 'headers.trackerInfo.triggeredDetails',
                    EventLog::EVENT_TEAM => 'headers.general.eventTeam',
                    EventLog::EVENT_SOURCE => 'headers.trackerInfo.eventSource',
                    EventLog::NTF_LIST => 'headers.general.notificationsList',
                ]
            ],
            'triggeredBy' => Event::BY_DRIVER
        ],
        [
            'implemented' => true,
            'name' => Event::ASSET_CREATED,
            'alias' => 'Asset created',
            'type' => Event::TYPE_USER,
            'entity' => Event::ENTITY_TYPE_ASSET,
            'scopes' => [
                ScopeType::ANY =>
                    [
                        ScopeType::GENERAL_SCOPE_CATEGORY =>
                            ScopeType::SCOPE_TO_SUBTYPES[ScopeType::GENERAL_SCOPE_CATEGORY][ScopeType::ANY],
                    ]
            ],
            'importance' => Importance::TYPE_NORMAL,
            Event::ADDITIONAL_SETTING => [
                Event::ADDITIONAL_SETTING_HEADER_COLUMNS => [
                    EventLog::EVENT_LOG_ID => 'headers.general.id',
                    EventLog::DATE => 'headers.general.formattedDate',
                    EventLog::IMPORTANCE => 'headers.general.importance',
                    EventLog::TRIGGERED_DETAILS => 'headers.assetInfo.createdBy',
                    EventLog::EVENT_TEAM => 'headers.general.eventTeam',
                    EventLog::EVENT_SOURCE => 'headers.assetInfo.eventSource',
                    EventLog::NTF_LIST => 'headers.general.notificationsList',
                ],
                Event::SETTING_EVENT_LOG => [
                    Event::ADDITIONAL_SETTING_MAPPING_FILTERS => [
                        EventLog::EVENT_ID => 'eventId',
                        EventLog::EVENT_LOG_ID => 'id',
                        EventLog::DATE => 'eventDate',
                        EventLog::TRIGGERED_DETAILS => 'userBy',
                        EventLog::EVENT_TEAM => 'entityTeamId',
                    ],
                ],
            ],
            'triggeredBy' => Event::BY_USER
        ],
        [
            'implemented' => true,
            'name' => Event::ASSET_DELETED,
            'alias' => 'Asset deleted',
            'type' => Event::TYPE_USER,
            'entity' => Event::ENTITY_TYPE_ASSET,
            'scopes' => [
                ScopeType::ASSET =>
                    [
                        ScopeType::GENERAL_SCOPE_CATEGORY =>
                            ScopeType::SCOPE_TO_SUBTYPES[ScopeType::GENERAL_SCOPE_CATEGORY][ScopeType::ASSET],
                    ]
            ],
            'importance' => Importance::TYPE_NORMAL,
            Event::ADDITIONAL_SETTING => [
                Event::ADDITIONAL_SETTING_HEADER_COLUMNS => [
                    EventLog::EVENT_LOG_ID => 'headers.general.id',
                    EventLog::DATE => 'headers.general.formattedDate',
                    EventLog::IMPORTANCE => 'headers.general.importance',
                    EventLog::TRIGGERED_DETAILS => 'headers.assetInfo.deletedBy',
                    EventLog::EVENT_TEAM => 'headers.general.eventTeam',
                    EventLog::EVENT_SOURCE => 'headers.assetInfo.eventSource',
                    EventLog::NTF_LIST => 'headers.general.notificationsList',
                ],
                Event::SETTING_EVENT_LOG => [
                    Event::ADDITIONAL_SETTING_MAPPING_FILTERS => [
                        EventLog::EVENT_ID => 'eventId',
                        EventLog::EVENT_LOG_ID => 'id',
                        EventLog::DATE => 'eventDate',
                        EventLog::TRIGGERED_DETAILS => 'userBy',
                        EventLog::EVENT_TEAM => 'entityTeamId',
                    ],
                ],
            ],
            'triggeredBy' => Event::BY_USER
        ],
        [
            'implemented' => true,
            'name' => Event::ASSET_MISSED,
            'alias' => 'Asset missed',
            'type' => Event::TYPE_USER,
            'entity' => Event::ENTITY_TYPE_ASSET,
            'scopes' => [
                ScopeType::ASSET =>
                    [
                        ScopeType::GENERAL_SCOPE_CATEGORY =>
                            ScopeType::SCOPE_TO_SUBTYPES[ScopeType::GENERAL_SCOPE_CATEGORY][ScopeType::ASSET],
                    ]
            ],
            'importance' => Importance::TYPE_NORMAL,
            Event::ADDITIONAL_SETTING => [
                Event::ADDITIONAL_SETTING_IS_TIME_DURATION => true,
                Event::ADDITIONAL_SETTING_HEADER_COLUMNS => [
                    EventLog::EVENT_LOG_ID => 'headers.general.id',
                    EventLog::DATE => 'headers.general.formattedDate',
                    EventLog::IMPORTANCE => 'headers.general.importance',
                    EventLog::EVENT_TEAM => 'headers.general.eventTeam',
                    EventLog::EVENT_SOURCE => 'headers.assetInfo.eventSource',
                    EventLog::NTF_LIST => 'headers.general.notificationsList',
                ],
                Event::SETTING_EVENT_LOG => [
                    Event::ADDITIONAL_SETTING_MAPPING_FILTERS => [
                        EventLog::EVENT_ID => 'eventId',
                        EventLog::EVENT_LOG_ID => 'id',
                        EventLog::DATE => 'eventDate',
                        EventLog::EVENT_TEAM => 'entityTeamId',
                    ],
                ],
            ],
            'triggeredBy' => Event::BY_SENSOR
        ],
        [
            'implemented' => true,
            'name' => Event::TRACKER_BATTERY_PERCENTAGE,
            'alias' => 'Battery Voltage Percentage',
            'type' => Event::TYPE_USER,
            'entity' => Event::ENTITY_TYPE_TRACKER_HISTORY,
            'scopes' => [
                ScopeType::VEHICLE =>
                    [
                        ScopeType::GENERAL_SCOPE_CATEGORY =>
                            ScopeType::SCOPE_TO_SUBTYPES[ScopeType::GENERAL_SCOPE_CATEGORY][ScopeType::TRACKER_HISTORY],
                    ]
            ],
            'importance' => Importance::TYPE_NORMAL,
            Event::ADDITIONAL_SETTING => [
                Event::ADDITIONAL_SETTING_IS_DEVICE_BATTERY => true,
                Event::ADDITIONAL_SETTING_HEADER_COLUMNS => [
                    EventLog::EVENT_LOG_ID => 'headers.general.id',
                    EventLog::DATE => 'headers.general.formattedDate',
                    EventLog::IMPORTANCE => 'headers.general.importance',
                    EventLog::TRIGGERED_DETAILS => 'headers.trackerInfo.triggeredDetails',
                    EventLog::EVENT_TEAM => 'headers.general.eventTeam',
                    EventLog::EVENT_SOURCE => 'headers.trackerInfo.eventSource',
                    EventLog::DEVICE_BATTERY_PERCENTAGE => 'headers.trackerInfo.deviceBatteryPercentage',
                    EventLog::LIMIT => 'headers.general.limit',
                    EventLog::NTF_LIST => 'headers.general.notificationsList',
                ],
                Event::SETTING_EVENT_LOG => [
                    Event::ADDITIONAL_SETTING_MAPPING_FILTERS => [
                        EventLog::EVENT_ID => 'eventId',
                        EventLog::EVENT_LOG_ID => 'id',
                        EventLog::DATE => 'eventDate',
                        EventLog::EVENT_TEAM => 'entityTeamId',
                        EventLog::TRIGGERED_DETAILS => 'driverId',
                        EventLog::EVENT_SOURCE => 'vehicleId',
                    ],
                ],
            ],
            'triggeredBy' => Event::BY_DRIVER
        ],
        [
            'implemented' => true,
            'name' => Event::STRIPE_INTEGRATION_ERROR,
            'alias' => 'Stripe integration error',
            'type' => Event::TYPE_USER,
            'entity' => Event::ENTITY_TYPE_TEAM,
            'scopes' => [
                ScopeType::TEAM =>
                    [
                        ScopeType::GENERAL_SCOPE_CATEGORY =>
                            ScopeType::SCOPE_TO_SUBTYPES[ScopeType::GENERAL_SCOPE_CATEGORY][ScopeType::TEAM],
                    ]
            ],
            'importance' => Importance::TYPE_NORMAL,
            'triggeredBy' => Event::BY_USER,
            Event::ADDITIONAL_SETTING => [
                Event::ADDITIONAL_SETTING_IS_LISTENER_TEAM => false,
                Event::ADDITIONAL_SETTING_HEADER_COLUMNS => [
                    EventLog::EVENT_LOG_ID => 'headers.general.id',
                    EventLog::DATE => 'headers.general.formattedDate',
                    EventLog::EVENT_TEAM => 'headers.general.eventTeam',
                    EventLog::SHORT_DETAILS => 'headers.trackerInfo.shortDetails',
                    EventLog::NTF_LIST => 'headers.general.notificationsList',
                ],
                Event::SETTING_EVENT_LOG => [
                    Event::ADDITIONAL_SETTING_MAPPING_FILTERS => [
                        EventLog::EVENT_LOG_ID => 'id',
                        EventLog::DATE => 'eventDate',
                        EventLog::EVENT_TEAM => 'entityTeamId',
                        EventLog::EVENT_ID => 'eventId',
                    ],
                ],
            ],
        ],
        [
            'implemented' => true,
            'name' => Event::STRIPE_PAYMENT_FAILED,
            'alias' => 'Stripe payment failed',
            'type' => Event::TYPE_USER,
            'entity' => Event::ENTITY_TYPE_INVOICE,
            'scopes' => [
                ScopeType::TEAM =>
                    [
                        ScopeType::GENERAL_SCOPE_CATEGORY =>
                            ScopeType::SCOPE_TO_SUBTYPES[ScopeType::GENERAL_SCOPE_CATEGORY][ScopeType::TEAM],
                    ]
            ],
            'importance' => Importance::TYPE_NORMAL,
            'triggeredBy' => Event::BY_USER,
            Event::ADDITIONAL_SETTING => [
                Event::ADDITIONAL_SETTING_IS_LISTENER_TEAM => false,
                Event::ADDITIONAL_SETTING_HEADER_COLUMNS => [
                    EventLog::EVENT_LOG_ID => 'headers.general.id',
                    EventLog::DATE => 'headers.general.formattedDate',
                    EventLog::EVENT_TEAM => 'headers.general.eventTeam',
                    EventLog::EXT_INVOICE_ID => 'headers.stripe.extInvoiceId',
                    EventLog::SHORT_DETAILS => 'headers.trackerInfo.shortDetails',
                    EventLog::NTF_LIST => 'headers.general.notificationsList',
                ],
                Event::SETTING_EVENT_LOG => [
                    Event::ADDITIONAL_SETTING_MAPPING_FILTERS => [
                        EventLog::EVENT_LOG_ID => 'id',
                        EventLog::DATE => 'eventDate',
                        EventLog::EVENT_TEAM => 'entityTeamId',
                        EventLog::EVENT_ID => 'eventId',
                    ],
                ],
            ],
        ],
        [
            'implemented' => true,
            'name' => Event::STRIPE_PAYMENT_SUCCESSFUL,
            'alias' => 'Stripe payment successful',
            'type' => Event::TYPE_USER,
            'entity' => Event::ENTITY_TYPE_INVOICE,
            'scopes' => [
                ScopeType::TEAM =>
                    [
                        ScopeType::GENERAL_SCOPE_CATEGORY =>
                            ScopeType::SCOPE_TO_SUBTYPES[ScopeType::GENERAL_SCOPE_CATEGORY][ScopeType::TEAM],
                    ]
            ],
            'importance' => Importance::TYPE_NORMAL,
            'triggeredBy' => Event::BY_USER,
            Event::ADDITIONAL_SETTING => [
                Event::ADDITIONAL_SETTING_IS_LISTENER_TEAM => false,
                Event::ADDITIONAL_SETTING_HEADER_COLUMNS => [
                    EventLog::EVENT_LOG_ID => 'headers.general.id',
                    EventLog::DATE => 'headers.general.formattedDate',
                    EventLog::EVENT_TEAM => 'headers.general.eventTeam',
                    EventLog::EXT_INVOICE_ID => 'headers.stripe.extInvoiceId',
                    EventLog::NTF_LIST => 'headers.general.notificationsList',
                ],
                Event::SETTING_EVENT_LOG => [
                    Event::ADDITIONAL_SETTING_MAPPING_FILTERS => [
                        EventLog::EVENT_LOG_ID => 'id',
                        EventLog::DATE => 'eventDate',
                        EventLog::EVENT_TEAM => 'entityTeamId',
                        EventLog::EVENT_ID => 'eventId',
                    ],
                ],
            ],
        ],
        [
            'implemented' => true,
            'name' => Event::XERO_INTEGRATION_ERROR,
            'alias' => 'Xero integration error',
            'type' => Event::TYPE_USER,
            'entity' => Event::ENTITY_TYPE_TEAM,
            'scopes' => [
                ScopeType::TEAM =>
                    [
                        ScopeType::GENERAL_SCOPE_CATEGORY =>
                            ScopeType::SCOPE_TO_SUBTYPES[ScopeType::GENERAL_SCOPE_CATEGORY][ScopeType::TEAM],
                    ]
            ],
            'importance' => Importance::TYPE_NORMAL,
            'triggeredBy' => Event::BY_USER,
            Event::ADDITIONAL_SETTING => [
                Event::ADDITIONAL_SETTING_IS_LISTENER_TEAM => false,
                Event::ADDITIONAL_SETTING_HEADER_COLUMNS => [
                    EventLog::EVENT_LOG_ID => 'headers.general.id',
                    EventLog::DATE => 'headers.general.formattedDate',
                    EventLog::EVENT_TEAM => 'headers.general.eventTeam',
                    EventLog::SHORT_DETAILS => 'headers.trackerInfo.shortDetails',
                    EventLog::NTF_LIST => 'headers.general.notificationsList',
                ],
                Event::SETTING_EVENT_LOG => [
                    Event::ADDITIONAL_SETTING_MAPPING_FILTERS => [
                        EventLog::EVENT_LOG_ID => 'id',
                        EventLog::DATE => 'eventDate',
                        EventLog::EVENT_TEAM => 'entityTeamId',
                        EventLog::EVENT_ID => 'eventId',
                    ],
                ],
            ],
        ],
        [
            'implemented' => true,
            'name' => Event::XERO_INVOICE_CREATION_ERROR,
            'alias' => 'Xero invoice creation error',
            'type' => Event::TYPE_USER,
            'entity' => Event::ENTITY_TYPE_INVOICE,
            'scopes' => [
                ScopeType::TEAM =>
                    [
                        ScopeType::GENERAL_SCOPE_CATEGORY =>
                            ScopeType::SCOPE_TO_SUBTYPES[ScopeType::GENERAL_SCOPE_CATEGORY][ScopeType::TEAM],
                    ]
            ],
            'importance' => Importance::TYPE_NORMAL,
            'triggeredBy' => Event::BY_USER,
            Event::ADDITIONAL_SETTING => [
                Event::ADDITIONAL_SETTING_IS_LISTENER_TEAM => false,
                Event::ADDITIONAL_SETTING_HEADER_COLUMNS => [
                    EventLog::EVENT_LOG_ID => 'headers.general.id',
                    EventLog::DATE => 'headers.general.formattedDate',
                    EventLog::EVENT_TEAM => 'headers.general.eventTeam',
                    EventLog::EXT_INVOICE_ID => 'headers.invoice.extInvoiceId',
                    EventLog::SHORT_DETAILS => 'headers.trackerInfo.shortDetails',
                    EventLog::NTF_LIST => 'headers.general.notificationsList',
                ],
                Event::SETTING_EVENT_LOG => [
                    Event::ADDITIONAL_SETTING_MAPPING_FILTERS => [
                        EventLog::EVENT_LOG_ID => 'id',
                        EventLog::DATE => 'eventDate',
                        EventLog::EVENT_TEAM => 'entityTeamId',
                        EventLog::EVENT_ID => 'eventId',
                    ],
                ],
            ],
        ],
        [
            'implemented' => true,
            'name' => Event::XERO_INVOICE_CREATED,
            'alias' => 'Xero invoice created',
            'type' => Event::TYPE_USER,
            'entity' => Event::ENTITY_TYPE_INVOICE,
            'scopes' => [
                ScopeType::TEAM =>
                    [
                        ScopeType::GENERAL_SCOPE_CATEGORY =>
                            ScopeType::SCOPE_TO_SUBTYPES[ScopeType::GENERAL_SCOPE_CATEGORY][ScopeType::TEAM],
                    ]
            ],
            'importance' => Importance::TYPE_NORMAL,
            'triggeredBy' => Event::BY_USER,
            Event::ADDITIONAL_SETTING => [
                Event::ADDITIONAL_SETTING_IS_LISTENER_TEAM => false,
                Event::ADDITIONAL_SETTING_HEADER_COLUMNS => [
                    EventLog::EVENT_LOG_ID => 'headers.general.id',
                    EventLog::DATE => 'headers.general.formattedDate',
                    EventLog::EVENT_TEAM => 'headers.general.eventTeam',
                    EventLog::EXT_INVOICE_ID => 'headers.xero.extInvoiceId',
                    EventLog::NTF_LIST => 'headers.general.notificationsList',
                ],
                Event::SETTING_EVENT_LOG => [
                    Event::ADDITIONAL_SETTING_MAPPING_FILTERS => [
                        EventLog::EVENT_LOG_ID => 'id',
                        EventLog::DATE => 'eventDate',
                        EventLog::EVENT_TEAM => 'entityTeamId',
                        EventLog::EVENT_ID => 'eventId',
                    ],
                ],
            ],
        ],
        [
            'implemented' => true,
            'name' => Event::XERO_PAYMENT_CREATION_ERROR,
            'alias' => 'Xero payment creation error',
            'type' => Event::TYPE_USER,
            'entity' => Event::ENTITY_TYPE_INVOICE,
            'scopes' => [
                ScopeType::TEAM =>
                    [
                        ScopeType::GENERAL_SCOPE_CATEGORY =>
                            ScopeType::SCOPE_TO_SUBTYPES[ScopeType::GENERAL_SCOPE_CATEGORY][ScopeType::TEAM],
                    ]
            ],
            'importance' => Importance::TYPE_NORMAL,
            'triggeredBy' => Event::BY_USER,
            Event::ADDITIONAL_SETTING => [
                Event::ADDITIONAL_SETTING_IS_LISTENER_TEAM => false,
                Event::ADDITIONAL_SETTING_HEADER_COLUMNS => [
                    EventLog::EVENT_LOG_ID => 'headers.general.id',
                    EventLog::DATE => 'headers.general.formattedDate',
                    EventLog::EVENT_TEAM => 'headers.general.eventTeam',
                    EventLog::EXT_INVOICE_ID => 'headers.xero.extInvoiceId',
                    EventLog::SHORT_DETAILS => 'headers.trackerInfo.shortDetails',
                    EventLog::NTF_LIST => 'headers.general.notificationsList',
                ],
                Event::SETTING_EVENT_LOG => [
                    Event::ADDITIONAL_SETTING_MAPPING_FILTERS => [
                        EventLog::EVENT_LOG_ID => 'id',
                        EventLog::DATE => 'eventDate',
                        EventLog::EVENT_TEAM => 'entityTeamId',
                        EventLog::EVENT_ID => 'eventId',
                    ],
                ],
            ],
        ],
        [
            'implemented' => true,
            'name' => Event::XERO_PAYMENT_CREATED,
            'alias' => 'Xero payment created',
            'type' => Event::TYPE_USER,
            'entity' => Event::ENTITY_TYPE_INVOICE,
            'scopes' => [
                ScopeType::TEAM =>
                    [
                        ScopeType::GENERAL_SCOPE_CATEGORY =>
                            ScopeType::SCOPE_TO_SUBTYPES[ScopeType::GENERAL_SCOPE_CATEGORY][ScopeType::TEAM],
                    ]
            ],
            'importance' => Importance::TYPE_NORMAL,
            'triggeredBy' => Event::BY_USER,
            Event::ADDITIONAL_SETTING => [
                Event::ADDITIONAL_SETTING_IS_LISTENER_TEAM => false,
                Event::ADDITIONAL_SETTING_HEADER_COLUMNS => [
                    EventLog::EVENT_LOG_ID => 'headers.general.id',
                    EventLog::DATE => 'headers.general.formattedDate',
                    EventLog::EVENT_TEAM => 'headers.general.eventTeam',
                    EventLog::EXT_INVOICE_ID => 'headers.xero.extInvoiceId',
                    EventLog::NTF_LIST => 'headers.general.notificationsList',
                ],
                Event::SETTING_EVENT_LOG => [
                    Event::ADDITIONAL_SETTING_MAPPING_FILTERS => [
                        EventLog::EVENT_LOG_ID => 'id',
                        EventLog::DATE => 'eventDate',
                        EventLog::EVENT_TEAM => 'entityTeamId',
                        EventLog::EVENT_ID => 'eventId',
                    ],
                ],
            ],
        ],
        [
            'implemented' => true,
            'name' => Event::INVOICE_CREATED,
            'alias' => 'Invoice created',
            'type' => Event::TYPE_USER,
            'entity' => Event::ENTITY_TYPE_INVOICE,
            'scopes' => [
                ScopeType::ANY =>
                    [
                        ScopeType::GENERAL_SCOPE_CATEGORY =>
                            ScopeType::SCOPE_TO_SUBTYPES[ScopeType::GENERAL_SCOPE_CATEGORY][ScopeType::ANY],
                    ]
            ],
            'importance' => Importance::TYPE_NORMAL,
            'triggeredBy' => Event::BY_USER,
            Event::ADDITIONAL_SETTING => [
                Event::ADDITIONAL_SETTING_HEADER_COLUMNS => [
                    EventLog::EVENT_LOG_ID => 'headers.general.id',
                    EventLog::DATE => 'headers.general.formattedDate',
                    EventLog::EVENT_TEAM => 'headers.general.eventTeam',
                    EventLog::EXT_INVOICE_ID => 'headers.invoice.extInvoiceId',
                    EventLog::NTF_LIST => 'headers.general.notificationsList',
                ],
                Event::SETTING_EVENT_LOG => [
                    Event::ADDITIONAL_SETTING_MAPPING_FILTERS => [
                        EventLog::EVENT_LOG_ID => 'id',
                        EventLog::DATE => 'eventDate',
                        EventLog::EVENT_TEAM => 'entityTeamId',
                        EventLog::EVENT_ID => 'eventId',
                    ],
                ],
            ],
        ],
        [
            'implemented' => true,
            'name' => Event::PAYMENT_FAILED,
            'alias' => 'Payment failed',
            'type' => Event::TYPE_USER,
            'entity' => Event::ENTITY_TYPE_INVOICE,
            'scopes' => [
                ScopeType::ANY =>
                    [
                        ScopeType::GENERAL_SCOPE_CATEGORY =>
                            ScopeType::SCOPE_TO_SUBTYPES[ScopeType::GENERAL_SCOPE_CATEGORY][ScopeType::ANY],
                    ]
            ],
            'importance' => Importance::TYPE_NORMAL,
            'triggeredBy' => Event::BY_USER,
            Event::ADDITIONAL_SETTING => [
                Event::ADDITIONAL_SETTING_HEADER_COLUMNS => [
                    EventLog::EVENT_LOG_ID => 'headers.general.id',
                    EventLog::DATE => 'headers.general.formattedDate',
                    EventLog::EVENT_TEAM => 'headers.general.eventTeam',
                    EventLog::EXT_INVOICE_ID => 'headers.payment.extInvoiceId',
                    EventLog::SHORT_DETAILS => 'headers.trackerInfo.shortDetails',
                    EventLog::NTF_LIST => 'headers.general.notificationsList',
                ],
                Event::SETTING_EVENT_LOG => [
                    Event::ADDITIONAL_SETTING_MAPPING_FILTERS => [
                        EventLog::EVENT_LOG_ID => 'id',
                        EventLog::DATE => 'eventDate',
                        EventLog::EVENT_TEAM => 'entityTeamId',
                        EventLog::EVENT_ID => 'eventId',
                    ],
                ],
            ],
        ],
        [
            'implemented' => true,
            'name' => Event::PAYMENT_SUCCESSFUL,
            'alias' => 'Payment successful',
            'type' => Event::TYPE_USER,
            'entity' => Event::ENTITY_TYPE_INVOICE,
            'scopes' => [
                ScopeType::ANY =>
                    [
                        ScopeType::GENERAL_SCOPE_CATEGORY =>
                            ScopeType::SCOPE_TO_SUBTYPES[ScopeType::GENERAL_SCOPE_CATEGORY][ScopeType::ANY],
                    ]
            ],
            'importance' => Importance::TYPE_NORMAL,
            'triggeredBy' => Event::BY_USER,
            Event::ADDITIONAL_SETTING => [
                Event::ADDITIONAL_SETTING_HEADER_COLUMNS => [
                    EventLog::EVENT_LOG_ID => 'headers.general.id',
                    EventLog::DATE => 'headers.general.formattedDate',
                    EventLog::EVENT_TEAM => 'headers.general.eventTeam',
                    EventLog::EXT_INVOICE_ID => 'headers.payment.extInvoiceId',
                    EventLog::NTF_LIST => 'headers.general.notificationsList',
                ],
                Event::SETTING_EVENT_LOG => [
                    Event::ADDITIONAL_SETTING_MAPPING_FILTERS => [
                        EventLog::EVENT_LOG_ID => 'id',
                        EventLog::DATE => 'eventDate',
                        EventLog::EVENT_TEAM => 'entityTeamId',
                        EventLog::EVENT_ID => 'eventId',
                    ],
                ],
            ],
        ],
        [
            'implemented' => true,
            'name' => Event::INVOICE_OVERDUE,
            'alias' => 'Invoice overdue',
            'type' => Event::TYPE_USER,
            'entity' => Event::ENTITY_TYPE_INVOICE,
            'scopes' => [
                ScopeType::ANY =>
                    [
                        ScopeType::GENERAL_SCOPE_CATEGORY =>
                            ScopeType::SCOPE_TO_SUBTYPES[ScopeType::GENERAL_SCOPE_CATEGORY][ScopeType::ANY],
                    ]
            ],
            'importance' => Importance::TYPE_NORMAL,
            'triggeredBy' => Event::BY_USER,
            Event::ADDITIONAL_SETTING => [
                Event::ADDITIONAL_SETTING_HEADER_COLUMNS => [
                    EventLog::EVENT_LOG_ID => 'headers.general.id',
                    EventLog::DATE => 'headers.general.formattedDate',
                    EventLog::EVENT_TEAM => 'headers.general.eventTeam',
                    EventLog::EXT_INVOICE_ID => 'headers.invoice.extInvoiceId',
                    EventLog::NTF_LIST => 'headers.general.notificationsList',
                ],
                Event::SETTING_EVENT_LOG => [
                    Event::ADDITIONAL_SETTING_MAPPING_FILTERS => [
                        EventLog::EVENT_LOG_ID => 'id',
                        EventLog::DATE => 'eventDate',
                        EventLog::EVENT_TEAM => 'entityTeamId',
                        EventLog::EVENT_ID => 'eventId',
                    ],
                ],
            ],
        ],
        [
            'implemented' => true,
            'name' => Event::INVOICE_OVERDUE_PARTIALLY_BLOCKED,
            'alias' => 'Invoice overdue - Account partially blocked',
            'type' => Event::TYPE_USER,
            'entity' => Event::ENTITY_TYPE_INVOICE,
            'scopes' => [
                ScopeType::ANY =>
                    [
                        ScopeType::GENERAL_SCOPE_CATEGORY =>
                            ScopeType::SCOPE_TO_SUBTYPES[ScopeType::GENERAL_SCOPE_CATEGORY][ScopeType::ANY],
                    ]
            ],
            'importance' => Importance::TYPE_NORMAL,
            'triggeredBy' => Event::BY_USER,
            Event::ADDITIONAL_SETTING => [
                Event::ADDITIONAL_SETTING_HEADER_COLUMNS => [
                    EventLog::EVENT_LOG_ID => 'headers.general.id',
                    EventLog::DATE => 'headers.general.formattedDate',
                    EventLog::EVENT_TEAM => 'headers.general.eventTeam',
                    EventLog::EXT_INVOICE_ID => 'headers.invoice.extInvoiceId',
                    EventLog::NTF_LIST => 'headers.general.notificationsList',
                ],
                Event::SETTING_EVENT_LOG => [
                    Event::ADDITIONAL_SETTING_MAPPING_FILTERS => [
                        EventLog::EVENT_LOG_ID => 'id',
                        EventLog::DATE => 'eventDate',
                        EventLog::EVENT_TEAM => 'entityTeamId',
                        EventLog::EVENT_ID => 'eventId',
                    ],
                ],
            ],
        ],
        [
            'implemented' => true,
            'name' => Event::INVOICE_OVERDUE_BLOCKED,
            'alias' => 'Invoice overdue - Account blocked',
            'type' => Event::TYPE_USER,
            'entity' => Event::ENTITY_TYPE_INVOICE,
            'scopes' => [
                ScopeType::ANY =>
                    [
                        ScopeType::GENERAL_SCOPE_CATEGORY =>
                            ScopeType::SCOPE_TO_SUBTYPES[ScopeType::GENERAL_SCOPE_CATEGORY][ScopeType::ANY],
                    ]
            ],
            'importance' => Importance::TYPE_NORMAL,
            'triggeredBy' => Event::BY_USER,
            Event::ADDITIONAL_SETTING => [
                Event::ADDITIONAL_SETTING_HEADER_COLUMNS => [
                    EventLog::EVENT_LOG_ID => 'headers.general.id',
                    EventLog::DATE => 'headers.general.formattedDate',
                    EventLog::EVENT_TEAM => 'headers.general.eventTeam',
                    EventLog::EXT_INVOICE_ID => 'headers.invoice.extInvoiceId',
                    EventLog::NTF_LIST => 'headers.general.notificationsList',
                ],
                Event::SETTING_EVENT_LOG => [
                    Event::ADDITIONAL_SETTING_MAPPING_FILTERS => [
                        EventLog::EVENT_LOG_ID => 'id',
                        EventLog::DATE => 'eventDate',
                        EventLog::EVENT_TEAM => 'entityTeamId',
                        EventLog::EVENT_ID => 'eventId',
                    ],
                ],
            ],
        ],
        [
            'implemented' => true,
            'name' => Event::INVOICE_CREATED_ADMIN,
            'alias' => 'Invoice created admin',
            'type' => Event::TYPE_USER,
            'entity' => Event::ENTITY_TYPE_INVOICE,
            'scopes' => [
                ScopeType::TEAM =>
                    [
                        ScopeType::GENERAL_SCOPE_CATEGORY =>
                            ScopeType::SCOPE_TO_SUBTYPES[ScopeType::GENERAL_SCOPE_CATEGORY][ScopeType::TEAM],
                    ]
            ],
            'importance' => Importance::TYPE_NORMAL,
            'triggeredBy' => Event::BY_USER,
            Event::ADDITIONAL_SETTING => [
                Event::ADDITIONAL_SETTING_IS_LISTENER_TEAM => false,
                Event::ADDITIONAL_SETTING_HEADER_COLUMNS => [
                    EventLog::EVENT_LOG_ID => 'headers.general.id',
                    EventLog::DATE => 'headers.general.formattedDate',
                    EventLog::EVENT_TEAM => 'headers.general.eventTeam',
                    EventLog::EXT_INVOICE_ID => 'headers.invoice.extInvoiceId',
                    EventLog::NTF_LIST => 'headers.general.notificationsList',
                ],
                Event::SETTING_EVENT_LOG => [
                    Event::ADDITIONAL_SETTING_MAPPING_FILTERS => [
                        EventLog::EVENT_LOG_ID => 'id',
                        EventLog::DATE => 'eventDate',
                        EventLog::EVENT_TEAM => 'entityTeamId',
                        EventLog::EVENT_ID => 'eventId',
                    ],
                ],
            ],
        ],
        [
            'implemented' => true,
            'name' => Event::INVOICE_OVERDUE_ADMIN,
            'alias' => 'Invoice overdue',
            'type' => Event::TYPE_USER,
            'entity' => Event::ENTITY_TYPE_INVOICE,
            'scopes' => [
                ScopeType::TEAM =>
                    [
                        ScopeType::GENERAL_SCOPE_CATEGORY =>
                            ScopeType::SCOPE_TO_SUBTYPES[ScopeType::GENERAL_SCOPE_CATEGORY][ScopeType::TEAM],
                    ]
            ],
            'importance' => Importance::TYPE_NORMAL,
            'triggeredBy' => Event::BY_USER,
            Event::ADDITIONAL_SETTING => [
                Event::ADDITIONAL_SETTING_IS_LISTENER_TEAM => false,
                Event::ADDITIONAL_SETTING_HEADER_COLUMNS => [
                    EventLog::EVENT_LOG_ID => 'headers.general.id',
                    EventLog::DATE => 'headers.general.formattedDate',
                    EventLog::EVENT_TEAM => 'headers.general.eventTeam',
                    EventLog::EXT_INVOICE_ID => 'headers.invoice.extInvoiceId',
                    EventLog::NTF_LIST => 'headers.general.notificationsList',
                ],
                Event::SETTING_EVENT_LOG => [
                    Event::ADDITIONAL_SETTING_MAPPING_FILTERS => [
                        EventLog::EVENT_LOG_ID => 'id',
                        EventLog::DATE => 'eventDate',
                        EventLog::EVENT_TEAM => 'entityTeamId',
                        EventLog::EVENT_ID => 'eventId',
                    ],
                ],
            ],
        ],
        [
            'implemented' => true,
            'name' => Event::INVOICE_OVERDUE_PARTIALLY_BLOCKED_ADMIN,
            'alias' => 'Invoice overdue - Account partially blocked',
            'type' => Event::TYPE_USER,
            'entity' => Event::ENTITY_TYPE_INVOICE,
            'scopes' => [
                ScopeType::TEAM =>
                    [
                        ScopeType::GENERAL_SCOPE_CATEGORY =>
                            ScopeType::SCOPE_TO_SUBTYPES[ScopeType::GENERAL_SCOPE_CATEGORY][ScopeType::TEAM],
                    ]
            ],
            'importance' => Importance::TYPE_NORMAL,
            'triggeredBy' => Event::BY_USER,
            Event::ADDITIONAL_SETTING => [
                Event::ADDITIONAL_SETTING_IS_LISTENER_TEAM => false,
                Event::ADDITIONAL_SETTING_HEADER_COLUMNS => [
                    EventLog::EVENT_LOG_ID => 'headers.general.id',
                    EventLog::DATE => 'headers.general.formattedDate',
                    EventLog::EVENT_TEAM => 'headers.general.eventTeam',
                    EventLog::EXT_INVOICE_ID => 'headers.invoice.extInvoiceId',
                    EventLog::NTF_LIST => 'headers.general.notificationsList',
                ],
                Event::SETTING_EVENT_LOG => [
                    Event::ADDITIONAL_SETTING_MAPPING_FILTERS => [
                        EventLog::EVENT_LOG_ID => 'id',
                        EventLog::DATE => 'eventDate',
                        EventLog::EVENT_TEAM => 'entityTeamId',
                        EventLog::EVENT_ID => 'eventId',
                    ],
                ],
            ],
        ],
        [
            'implemented' => true,
            'name' => Event::INVOICE_OVERDUE_BLOCKED_ADMIN,
            'alias' => 'Invoice overdue - Account blocked',
            'type' => Event::TYPE_USER,
            'entity' => Event::ENTITY_TYPE_INVOICE,
            'scopes' => [
                ScopeType::TEAM =>
                    [
                        ScopeType::GENERAL_SCOPE_CATEGORY =>
                            ScopeType::SCOPE_TO_SUBTYPES[ScopeType::GENERAL_SCOPE_CATEGORY][ScopeType::TEAM],
                    ]
            ],
            'importance' => Importance::TYPE_NORMAL,
            'triggeredBy' => Event::BY_USER,
            Event::ADDITIONAL_SETTING => [
                Event::ADDITIONAL_SETTING_IS_LISTENER_TEAM => false,
                Event::ADDITIONAL_SETTING_HEADER_COLUMNS => [
                    EventLog::EVENT_LOG_ID => 'headers.general.id',
                    EventLog::DATE => 'headers.general.formattedDate',
                    EventLog::EVENT_TEAM => 'headers.general.eventTeam',
                    EventLog::EXT_INVOICE_ID => 'headers.invoice.extInvoiceId',
                    EventLog::NTF_LIST => 'headers.general.notificationsList',
                ],
                Event::SETTING_EVENT_LOG => [
                    Event::ADDITIONAL_SETTING_MAPPING_FILTERS => [
                        EventLog::EVENT_LOG_ID => 'id',
                        EventLog::DATE => 'eventDate',
                        EventLog::EVENT_TEAM => 'entityTeamId',
                        EventLog::EVENT_ID => 'eventId',
                    ],
                ],
            ],
        ],
        [
            'implemented' => true,
            'name' => Event::DEVICE_CONTRACT_EXPIRED,
            'alias' => 'Device contract expired',
            'type' => Event::TYPE_USER,
            'entity' => Event::ENTITY_TYPE_DEVICE,
            'scopes' => [
                ScopeType::TEAM =>
                    [
                        ScopeType::GENERAL_SCOPE_CATEGORY =>
                            ScopeType::SCOPE_TO_SUBTYPES[ScopeType::GENERAL_SCOPE_CATEGORY][ScopeType::TEAM],
                    ]
            ],
            'importance' => Importance::TYPE_NORMAL,
            'triggeredBy' => Event::BY_USER,
            Event::ADDITIONAL_SETTING => [
                Event::ADDITIONAL_SETTING_IS_LISTENER_TEAM => false,
                Event::ADDITIONAL_SETTING_HEADER_COLUMNS => [
                    EventLog::EVENT_LOG_ID => 'headers.general.id',
                    EventLog::DATE => 'headers.general.formattedDate',
                    EventLog::IMPORTANCE => 'headers.general.importance',
                    EventLog::TRIGGERED_DETAILS => 'headers.deviceInfo.triggeredDetails',
                    EventLog::EVENT_TEAM => 'headers.general.eventTeam',
                    EventLog::EVENT_SOURCE => 'headers.deviceInfo.eventSource',
                    EventLog::NTF_LIST => 'headers.general.notificationsList',
                ],
                Event::SETTING_EVENT_LOG => [
                    Event::ADDITIONAL_SETTING_MAPPING_FILTERS => [
                        EventLog::EVENT_LOG_ID => 'id',
                        EventLog::DATE => 'eventDate',
                        EventLog::TRIGGERED_DETAILS => 'userBy',
                        EventLog::EVENT_TEAM => 'entityTeamId',
                        EventLog::EVENT_SOURCE => 'entityId',
                        EventLog::EVENT_ID => 'eventId',
                    ],
                ],
            ],
        ],
        [
            'implemented' => true,
            'name' => Event::EXCEEDING_SPEED_LIMIT,
            'alias' => 'Exceeding speed limit',
            'type' => Event::TYPE_USER,
            'entity' => Event::ENTITY_TYPE_TRACKER_HISTORY,
            'scopes' => [
                ScopeType::VEHICLE =>
                    [
                        ScopeType::GENERAL_SCOPE_CATEGORY =>
                            ScopeType::SCOPE_TO_SUBTYPES[ScopeType::GENERAL_SCOPE_CATEGORY][ScopeType::VEHICLE],
                    ]
            ],
            'importance' => Importance::TYPE_NORMAL,
            Event::ADDITIONAL_SETTING => [
                Event::ADDITIONAL_SETTING_IS_TIME_DURATION => true,
                Event::ADDITIONAL_SETTING_IS_THRESHOLD_SPEED_LIMIT => true,
                Event::ADDITIONAL_SETTING_IS_DISTANCE => true,
                Event::ADDITIONAL_SETTING_IS_EXPRESSION_OPERATOR => true,
                Event::ADDITIONAL_SETTING_HEADER_COLUMNS => [
                    EventLog::EVENT_LOG_ID => 'headers.general.id',
                    EventLog::DATE => 'headers.general.formattedDate',
                    EventLog::IMPORTANCE => 'headers.general.importance',
                    EventLog::EVENT_TEAM => 'headers.general.eventTeam',
                    EventLog::TRIGGERED_DETAILS => 'headers.trackerInfo.triggeredDetails',
                    EventLog::EVENT_SOURCE => 'headers.trackerInfo.eventSource',
                    EventLog::VEHICLE_DEFAULT_LABEL => 'headers.vehicleInfo.title',
                    EventLog::SHORT_DETAILS => 'headers.trackerInfo.shortDetails',
                    EventLog::DEVICE_IMEI => 'headers.trackerInfo.imei',
                    EventLog::SPEED_LIMIT => 'headers.general.limit',
                    EventLog::MAX_SPEED => 'headers.trackerInfo.maxSpeed',
                    EventLog::SPEED_OVER_LIMIT_PERCENT => 'headers.trackerInfo.speedOverLimitPercent',
                    EventLog::DISTANCE => 'headers.trackerInfo.distance',
                    EventLog::DURATION => 'headers.trackerInfo.duration',
                    EventLog::ADDRESS => 'headers.trackerInfo.address',
                    EventLog::NTF_LIST => 'headers.general.notificationsList',
                ],
                Event::SETTING_EVENT_LOG => [
                    Event::ADDITIONAL_SETTING_MAPPING_FILTERS => [
                        EventLog::EVENT_ID => 'eventId',
                        EventLog::EVENT_LOG_ID => 'id',
                        EventLog::DATE => 'eventDate',
                        EventLog::EVENT_TEAM => 'entityTeamId',
                        EventLog::TRIGGERED_DETAILS => 'driverId',
                        EventLog::EVENT_SOURCE => 'vehicleId',
                    ],
                ],
            ],
            'triggeredBy' => Event::BY_DRIVER
        ],

        [
            'implemented' => true,
            'name' => Event::TRACKER_ACCIDENT_HAPPENED_ALARM,
            'alias' => 'Accident happened',
            'type' => Event::TYPE_USER,
            'entity' => Event::ENTITY_TYPE_TRACKER_HISTORY,
            'scopes' => [
                ScopeType::VEHICLE =>
                    [
                        ScopeType::GENERAL_SCOPE_CATEGORY =>
                            ScopeType::SCOPE_TO_SUBTYPES[ScopeType::GENERAL_SCOPE_CATEGORY][ScopeType::VEHICLE],
                    ]
            ],
            'importance' => Importance::TYPE_NORMAL,
            Event::ADDITIONAL_SETTING => [
                Event::ADDITIONAL_SETTING_HEADER_COLUMNS => [
                    EventLog::EVENT_LOG_ID => 'headers.general.id',
                    EventLog::DATE => 'headers.general.formattedDate',
                    EventLog::IMPORTANCE => 'headers.general.importance',
                    EventLog::TRIGGERED_DETAILS => 'headers.trackerInfo.triggeredDetails',
                    EventLog::EVENT_TEAM => 'headers.general.eventTeam',
                    EventLog::EVENT_SOURCE => 'headers.trackerInfo.eventSource',
                    EventLog::SHORT_DETAILS => 'headers.trackerInfo.shortDetails',
                    EventLog::DEVICE_IMEI => 'headers.trackerInfo.imei',
                    EventLog::NTF_LIST => 'headers.general.notificationsList',
                ],
                Event::SETTING_EVENT_LOG => [
                    Event::ADDITIONAL_SETTING_MAPPING_FILTERS => [
                        EventLog::EVENT_ID => 'eventId',
                        EventLog::EVENT_LOG_ID => 'id',
                        EventLog::DATE => 'eventDate',
                        EventLog::EVENT_TEAM => 'entityTeamId',
                        EventLog::TRIGGERED_DETAILS => 'driverId',
                        EventLog::EVENT_SOURCE => 'vehicleId',
                    ],
                ],
            ],
            'triggeredBy' => Event::BY_DRIVER
        ],
        [
            'implemented' => true,
            'name' => Event::INTEGRATION_ENABLED,
            'alias' => 'Integration enabled',
            'type' => Event::TYPE_USER,
            'entity' => Event::ENTITY_TYPE_CLIENT,
            'scopes' => [
                ScopeType::TEAM =>
                    [
                        ScopeType::GENERAL_SCOPE_CATEGORY =>
                            ScopeType::SCOPE_TO_SUBTYPES[ScopeType::GENERAL_SCOPE_CATEGORY][ScopeType::TEAM],
                    ]
            ],
            'importance' => Importance::TYPE_NORMAL,
            'triggeredBy' => Event::BY_USER,
            Event::ADDITIONAL_SETTING => [
                Event::ADDITIONAL_SETTING_IS_LISTENER_TEAM => false,
                Event::ADDITIONAL_SETTING_HEADER_COLUMNS => [
                    EventLog::EVENT_LOG_ID => 'headers.general.id',
                    EventLog::DATE => 'headers.general.formattedDate',
                    EventLog::IMPORTANCE => 'headers.general.importance',
                    EventLog::TRIGGERED_DETAILS => 'headers.clientCreated.triggeredDetails',
                    EventLog::EVENT_TEAM => 'headers.general.eventTeam',
                    EventLog::NTF_LIST => 'headers.general.notificationsList',
                ],
                Event::SETTING_EVENT_LOG => [
                    Event::ADDITIONAL_SETTING_MAPPING_FILTERS => [
                        EventLog::EVENT_LOG_ID => 'id',
                        EventLog::DATE => 'eventDate',
                        EventLog::TRIGGERED_DETAILS => 'userBy',
                        EventLog::EVENT_TEAM => 'entityTeamId',
                        EventLog::EVENT_ID => 'eventId',
                    ],
                ],
            ],
        ],
        [
            'implemented' => true,
            'name' => Event::ACCESS_LEVEL_CHANGED,
            'alias' => 'Access level changed',
            'type' => Event::TYPE_USER,
            'entity' => Event::ENTITY_TYPE_USER,
            'scopes' => [
                ScopeType::USER =>
                    [
                        ScopeType::GENERAL_SCOPE_CATEGORY =>
                            ScopeType::SCOPE_TO_SUBTYPES[ScopeType::GENERAL_SCOPE_CATEGORY][ScopeType::USER],
                    ]
            ],
            'importance' => Importance::TYPE_NORMAL,
            'triggeredBy' => Event::BY_USER,
            Event::ADDITIONAL_SETTING => [
                Event::ADDITIONAL_SETTING_HEADER_COLUMNS => [
                    EventLog::EVENT_LOG_ID => 'headers.general.id',
                    EventLog::DATE => 'headers.general.formattedDate',
                    EventLog::IMPORTANCE => 'headers.general.importance',
                    EventLog::TRIGGERED_DETAILS => 'headers.userBlocked.triggeredDetails',
                    EventLog::EVENT_TEAM => 'headers.general.eventTeam',
                    EventLog::EVENT_SOURCE => 'headers.userBlocked.eventSource',
                    EventLog::NTF_LIST => 'headers.general.notificationsList',
                ],
                Event::SETTING_EVENT_LOG => [
                    Event::ADDITIONAL_SETTING_MAPPING_FILTERS => [
                        EventLog::EVENT_LOG_ID => 'id',
                        EventLog::DATE => 'eventDate',
                        EventLog::TRIGGERED_DETAILS => 'userBy',
                        EventLog::EVENT_TEAM => 'entityTeamId',
                        EventLog::EVENT_ID => 'eventId',
                    ],
                ],
            ],
        ],
    ];

    /**
     * @return array
     */
    public function getDependencies(): array
    {
        return [
            InitScopeTypesFixture::class,
            IntImportanceFixture::class,
        ];
    }

    /**
     * @return array
     */
    public static function getGroups(): array
    {
        return [FixturesTypes::GLOBAL];
    }

    /**
     * @param ObjectManager $manager
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function load(ObjectManager $manager): void
    {
        /** @var EntityManager $manager */
        $manager = $this->prepareEntityManager($manager);

        foreach (self::DATA as $eventData) {
            /** @var Event $event */
            $event = $manager->getRepository(Event::class)->findOneBy(
                [
                    'name' => $eventData['name'],
                    'type' => $eventData['type']
                ]
            );

            if (!$event) {
                $event = new Event();
                $event
                    ->setName(sprintf(!$eventData['implemented'] ? '*%s' : '%s', $eventData['name']))
                    ->setAlias($eventData['alias'])
                    ->setType($eventData['type'])
                    ->setEntity($eventData['entity'])
                    ->setImportance($this->getReference(implode('_', [$eventData['importance']])))
                    ->setAdditionalSettings($eventData[Event::ADDITIONAL_SETTING] ?? null)
                    ->setTriggeredBy($eventData['triggeredBy'] ?? null);

                foreach ($eventData['scopes'] as $entity => $scopes) {
                    foreach ($scopes as $category => $scopeSubTypes) {
                        foreach ($scopeSubTypes as $scopeSubType) {
                            $event->addScopeType(
                                $this->getReference(implode('_', [$entity, $scopeSubType, $category]))
                            );
                        }
                    }
                }
                $manager->persist($event);
            } else {
                $event
                    ->setName(sprintf(!$eventData['implemented'] ? '*%s' : '%s', $eventData['name']))
                    ->setAlias($eventData['alias'])
                    ->setEntity($eventData['entity'])
                    ->setAdditionalSettings($eventData[Event::ADDITIONAL_SETTING] ?? null)
                    ->setTriggeredBy($eventData['triggeredBy'] ?? null);

                foreach ($eventData['scopes'] as $entity => $scopes) {
                    foreach ($scopes as $category => $scopeSubTypes) {
                        // Delete  uninstalled scopes
                        foreach ($event->getScopeTypeByCategory($category) as $scopeByEvent) {
                            /** @var ScopeType $scopeByEvent */
                            if (!in_array($scopeByEvent->getSubType(), $scopeSubTypes)) {
                                $event->removeScopeType($scopeByEvent);
                            }
                        }

                        foreach ($scopeSubTypes as $scopeSubType) {
                            $checkSubType = $event->scopeTypeAllowed($category, $scopeSubType);

                            if (!$checkSubType) {
                                $event->addScopeType(
                                    $this->getReference(implode('_', [$entity, $scopeSubType, $category]))
                                );
                            }
                        }
                    }
                }
            }
            $this->setReference(implode('_', [$eventData['name'], $eventData['type']]), $event);
        }

        $manager->flush();
    }
}
