<?php

namespace App\Service\Notification\Placeholder\EntityPlaceholder\DeviceEntity;

use App\Service\Notification\Placeholder\AbstractEntityPlaceholder;

class DeviceInStockPlaceholder extends AbstractEntityPlaceholder
{
//    public function getPlaceholder(): array
//    {
//        return [
//            Event::DEVICE_IN_STOCK => [
//                Event::TYPE_USER => function (Device $entity) use ($event) {
//                    return [
//                        'device_imei' => $entity->getImei(),
//                        'status' => $entity->getStatus(),
//                        'event_time' => $entity->getCreatedAt()
//                            ? DateHelper::formatDate(
//                                $entity->getCreatedAt(),
//                                DateHelper::FORMAT_DATE_SHORT_TIME,
//                                $entity->getTimeZoneName()
//                            ) : null,
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
            'event_time' => 'createdTime',
            'data_url' => 'dataUrl',
        ];
    }
}
