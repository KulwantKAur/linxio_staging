<?php

namespace App\Service\Notification\DefaultNotification;

use App\Entity\Notification\Notification;
use App\Entity\Notification\NotificationRecipients;
use App\Entity\Notification\ScopeType;

class ChevronDefaultNotification
{
    public const DATA = [
        [
            'acknowledgeRecipients' => [],
            'additionalParams' => [
                "timeDuration" => 0,
                "deviceVoltage" => 5,
                "distance" => 0,
                "areaTriggerType" => "everywhere",
                "exprOperator" => "or"
            ],
            'additionalScope' => [
                'subtype' => ScopeType::SUBTYPE_ANY,
                'value' => ''
            ],
            'comment' => '',
            'eventId' => 52,
            'eventTrackingDays' => Notification::ALL_EVENT_TRACKING_DAYS,
            'eventTrackingTimeFrom' => Notification::DEFAULT_EVENT_TRACKING_TIME_FROM,
            'eventTrackingTimeUntil' => Notification::DEFAULT_EVENT_TRACKING_TIME_UNTIL,
            'importance' => "immediately",
            'recipients' => [
                [
                    "type" => NotificationRecipients::TYPE_ROLE,
                    "value" => [6]
                ],
                [
                    "type" => NotificationRecipients::TYPE_OTHER_EMAIL,
                    "value" => []
                ]
            ],
            'scope' => [
                "subtype" => ScopeType::SUBTYPE_ANY,
                "value" => ""
            ],
            'status' => Notification::STATUS_ENABLED,
            'teamId' => null,
            'title' => "Supply Voltage",
            'transports' => [
                "email",
                "inApp"
            ]
        ]
    ];
}