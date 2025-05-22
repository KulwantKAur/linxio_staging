<?php

namespace App\Service\Notification\Placeholder\EntityPlaceholder\TrackerHistoryEntity;

use App\Service\Notification\Placeholder\AbstractEntityPlaceholder;

class PanicButtonPlaceholder extends AbstractEntityPlaceholder
{
//    public function getPlaceholder(): array
//    {
//        return [
//            Event::PANIC_BUTTON => [
//                Event::TYPE_USER => function (TrackerHistory $entity) {
//                    return [
//                        'device_imei' => $entity->getDevice() ? $entity->getDevice()->getImei() : null,
//                        'reg_no' => $entity->getVehicle()
//                            ? $entity->getVehicle()->getRegNo() : self::DEFAULT_UNKNOWN,
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
            'reg_no_or_device' => 'regNoOrDevice',
            'reg_no_with_model_or_device' => 'regNoWithModelOrDevice',
            'driver' => 'driver',
            'event_time' => 'createdTime',
            'vehicle_url' => 'vehicleUrl',
            'driver_url' => 'driverUrl',
            'google_url' => 'googleUrl',
        ];
    }
}
