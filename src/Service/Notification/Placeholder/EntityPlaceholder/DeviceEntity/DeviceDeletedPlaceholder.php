<?php

namespace App\Service\Notification\Placeholder\EntityPlaceholder\DeviceEntity;

use App\Service\Notification\Placeholder\AbstractEntityPlaceholder;

class DeviceDeletedPlaceholder extends AbstractEntityPlaceholder
{
//    public function getPlaceholder(): array
//    {
//        return [
//            Event::DEVICE_DELETED => [
//                Event::TYPE_USER => function (Device $entity) use ($event) {
//                    return [
//                        'device_imei' => $entity->getImei(),
//                        'status' => $entity->getStatus(),
//                        'event_time' => $entity->getUpdatedAt()
//                            ? DateHelper::formatDate(
//                                $entity->getUpdatedAt(),
//                                DateHelper::FORMAT_DATE_SHORT_TIME,
//                                $entity->getTimeZoneName()
//                            ) : null,
//                        'triggered_by' => $entity->getCreatedBy()
//                            ? $entity->getCreatedBy()->getFullName() : self::DEFAULT_UNKNOWN,
//                        'data_url' => $this->getFrontendLinks($event, $entity)['data_url'],
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
            'event_time' => 'updateTime',
            'triggered_by' => 'updateBy',
            'data_url' => 'dataUrl',
        ];
    }
}
