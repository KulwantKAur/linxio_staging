<?php

namespace App\Service\Notification\Placeholder\EntityPlaceholder\TrackerHistoryEntity;

use App\Service\Notification\Placeholder\AbstractEntityPlaceholder;

class VehicleTowingPlaceholder extends AbstractEntityPlaceholder
{
//    public function getPlaceholder(): array
//    {
//        return [
//            Event::VEHICLE_TOWING_EVENT => [
//                Event::TYPE_USER => function (TrackerHistory $entity) {
//                    return [
//                        'reg_no' => $entity->getVehicle() ? $entity->getVehicle()->getRegNo() : null,
//                        'event_time' => $entity->getTs()
//                            ? DateHelper::formatDate(
//                                $entity->getTs(),
//                                DateHelper::FORMAT_DATE_SHORT_TIME,
//                                $entity->getTimeZoneName()
//                            ) : null,
//                        'reg_no_with_model' => $entity->getId()
//                            ? !empty($entity->getVehicle()->getModel())
//                                ? vsprintf(
//                                    '%s (%s)',
//                                    [$entity->getVehicle()->getRegNo(), $entity->getVehicle()->getModel()]
//                                )
//                                : vsprintf('%s', [$entity->getVehicle()->getRegNo()])
//                            : self::DEFAULT_UNKNOWN,
//                    ];
//                },
//            ],
//
//        ];
//    }

    public function getInternalMappedPlaceholder(): array
    {
        return [
            'from_company' => 'fromCompany',
            'reg_no' => 'regNo',
            'event_time' => 'tsTime',
            'reg_no_with_model' => 'regNoWithModel',
        ];
    }
}
