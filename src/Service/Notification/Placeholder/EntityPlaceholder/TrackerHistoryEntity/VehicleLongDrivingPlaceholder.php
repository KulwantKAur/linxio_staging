<?php

namespace App\Service\Notification\Placeholder\EntityPlaceholder\TrackerHistoryEntity;

use App\Service\Notification\Placeholder\AbstractEntityPlaceholder;

class VehicleLongDrivingPlaceholder extends AbstractEntityPlaceholder
{
//    public function getPlaceholder(): array
//    {
//        return [
//            Event::VEHICLE_LONG_DRIVING => [
//                Event::TYPE_USER => function (Route $entity) use ($event) {
//                    return [
//                        'reg_no' => $entity->getVehicle() ? $entity->getVehicle()->getRegNo() : null,
//                        'event_time' => $entity->getStartedAt()
//                            ? DateHelper::formatDate(
//                                $entity->getStartedAt(),
//                                DateHelper::FORMAT_DATE_SHORT_TIME,
//                                $entity->getTimeZoneName()
//                            ) : null,
//                        'model' => $entity->getVehicle()
//                            ? $entity->getVehicle()->getModel() : self::DEFAULT_UNKNOWN,
//                        'driver' => $entity->getVehicle()
//                            ? ($entity->getVehicle()->getDriverName() ?? self::DEFAULT_UNKNOWN)
//                            : self::DEFAULT_UNKNOWN,
//                        'duration' =>
//                            $entity->getDuration() ? DateHelper::seconds2human($entity->getDuration()) : null,
//                        'distance' => $entity->getDistance()
//                            ? MetricHelper::metersToHumanKm($entity->getDistance()) : null,
//                        $entity->getDuration() ? DateHelper::seconds2human($entity->getDuration()) : null,
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
            'event_time' => 'tsTime',
            'model' => 'model',
            'driver' => 'driver',
            'duration' => 'duration',
            'distance' => 'distance',
            'reg_no_with_model' => 'regNoWithModel',
            'vehicle_url' => 'vehicleUrl',
            'driver_url' => 'driverUrl',
        ];
    }
}
