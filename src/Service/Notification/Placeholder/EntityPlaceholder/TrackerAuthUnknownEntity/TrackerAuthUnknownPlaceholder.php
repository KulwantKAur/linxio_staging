<?php

namespace App\Service\Notification\Placeholder\EntityPlaceholder\TrackerAuthUnknownEntity;

use App\Service\Notification\Placeholder\AbstractEntityPlaceholder;

class TrackerAuthUnknownPlaceholder extends AbstractEntityPlaceholder
{
//    public function getPlaceholder(): array
//    {
//        return [
//            Event::DEVICE_UNKNOWN_DETECTED => [
//                Event::TYPE_USER => function (TrackerAuthUnknown $entity) {
//                    return [
//                        'device_imei' => $entity->getImei(),
//                        'status' => '-',
//                        'event_time' => $entity->getCreatedAt()
//                            ? DateHelper::formatDate(
//                                $entity->getCreatedAt(),
//                                DateHelper::FORMAT_DATE_SHORT_TIME,
//                                $entity->getTimeZoneName()
//                            ) : null,
//                    ];
//                },
//            ],
//        ];
//    }

    public function getInternalMappedPlaceholder(): array
    {
        return [
            'from_company' => 'fromCompany',
            'device_imei' => 'deviceImei',
            'status' => 'status',
            'event_time' => 'createdTime',
        ];
    }
}
