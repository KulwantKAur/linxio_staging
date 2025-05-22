<?php

namespace App\Service\Notification\Placeholder\EntityPlaceholder\TrackerHistoryEntity;

use App\Service\Notification\Placeholder\AbstractEntityPlaceholder;

class VehicleMovingPlaceholder extends AbstractEntityPlaceholder
{
//    public function getPlaceholder(): array
//    {
//        return [
//            Event::VEHICLE_MOVING => [
//                Event::TYPE_USER => function (Route $entity) use ($event) {
//                    return [
//                        'reg_no' => $entity->getVehicle()
//                            ? $entity->getVehicle()->getRegNo() : self::DEFAULT_UNKNOWN,
//                        'driver' => $entity->getVehicle()
//                            ? ($entity->getVehicle()->getDriverName() ?? self::DEFAULT_UNKNOWN)
//                            : self::DEFAULT_UNKNOWN,
//                        'event_time' => $entity->getStartedAt()
//                            ? DateHelper::formatDate(
//                                $entity->getStartedAt(),
//                                DateHelper::FORMAT_DATE_SHORT_TIME,
//                                $entity->getTimeZoneName()
//                            ) : self::DEFAULT_UNKNOWN,
//                        'model' => $entity->getVehicle()
//                            ? $entity->getVehicle()->getModel()
//                            : null,
//                        'reg_no_with_model' => $entity->getVehicle()
//                            ? !empty($entity->getVehicle()->getModel())
//                                ? vsprintf(
//                                    '%s (%s)',
//                                    [$entity->getVehicle()->getRegNo(), $entity->getVehicle()->getModel()]
//                                )
//                                : vsprintf('%s', [$entity->getVehicle()->getRegNo()])
//                            : self::DEFAULT_UNKNOWN,
//                        'vehicle_url' => $this->getFrontendLinks($event, $entity)['vehicle_url'],
//                        'driver_url' => $this->getFrontendLinks($event, $entity)['driver_url'],
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
            'driver' => 'driver',
            'duration' =>'duration',
            'distance' => 'distance',
            'event_time' => 'tsTime',
            'model' => 'model',
            'reg_no_with_model' => 'regNoWithModel',
            'vehicle_url' => 'vehicleUrl',
            'driver_url' => 'driverUrl',
        ];
    }
}
