<?php

namespace App\Service\Notification\Placeholder\EntityPlaceholder\VehicleOdometerEntity;

use App\Service\Notification\Placeholder\AbstractEntityPlaceholder;

class VehicleOdometerPlaceholder extends AbstractEntityPlaceholder
{
//    public function getPlaceholder(): array
//    {
//        return [
//            Event::ODOMETER_CORRECTED => [
//                Event::TYPE_USER => function (VehicleOdometer $entity) use ($context) {
//                    return [
//                        'reg_no' => $entity->getVehicle()
//                            ? $entity->getVehicle()->getRegNo() : self::DEFAULT_UNKNOWN,
//                        'event_time' => DateHelper::formatDate(
//                            $entity->getCreatedAt(),
//                            DateHelper::FORMAT_DATE_SHORT_TIME,
//                            $entity->getVehicle() ? $entity->getVehicle()->getTimeZoneName() : null
//                        ),
//                        'user_name' => $entity->getCreatedBy()->getFullName(),
//                        'new_value' => (int)($entity->getOdometer() / 1000),
//                        'old_value' => !empty($context['oldValue']) ? (int)($context['oldValue'] / 1000) : null,
//                    ];
//                },
//            ],
//        ];
//    }

    public function getInternalMappedPlaceholder(): array
    {
        return [
            'from_company' => 'fromCompany',
            'reg_no' => 'regNo',
            'event_time' => 'createdTime',
            'user_name' => 'createdBy',
            'new_value' => 'newValue',
            'old_value' => 'oldValue',
        ];
    }
}
