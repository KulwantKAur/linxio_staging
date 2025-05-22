<?php

namespace App\Service\Notification\Placeholder\EntityPlaceholder\AreaHistoryEntity;

use App\Service\Notification\Placeholder\AbstractEntityPlaceholder;

class VehicleOverSpeedingInsideAreaPlaceholder extends AbstractEntityPlaceholder
{
//    public function getPlaceholder(): array
//    {
//        return [
//            Event::VEHICLE_OVERSPEEDING_INSIDE_GEOFENCE => [
//                Event::TYPE_USER => function (AreaHistory $entity) use ($context, $event) {
//                    return [
//                        'area' => $entity->getArea()->getName(),
//                        'reg_no' => $entity->getVehicle()->getRegNo(),
//                        'model' => $entity->getVehicle()
//                            ? $entity->getVehicle()->getModel() : self::DEFAULT_UNKNOWN,
//                        'team' => $entity->getVehicle()
//                            ? $entity->getVehicle()->getTeam()->isClientTeam()
//                                ? $entity->getVehicle()->getTeam()->getClientName()
//                                : $entity->getVehicle()->getTeam()->getType()
//                            : self::DEFAULT_UNKNOWN,
//                        'driver' => $entity->getVehicle()->getDriverName()
//                            ? $entity->getVehicle()->getDriverName()
//                            : null,
//                        'avg_speed' => $context['speed'] ?? null,
//                        'status' => $entity->getVehicle()->getStatus(),
//                        'triggered_by' => $entity->getVehicle()->getUpdatedByName(),
//                        'event_time' => $entity->getArrived()
//                            ? DateHelper::formatDate(
//                                $entity->getArrived(),
//                                DateHelper::FORMAT_DATE_SHORT_TIME,
//                                $entity->getVehicle()->getTimeZoneName()
//                            ) : null,
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
            'area' => 'area',
            'reg_no' => 'regNo',
            'model' => 'model',
            'team' => 'team',
            'driver' => 'driver',
            'avg_speed' => 'avgSpeed',
            'status' => 'test',
            'triggered_by' => 'triggeredBy',
            'event_time' => 'arrivedTime',
            'reg_no_with_model' => 'regNoWithModel',
            'vehicle_url' => 'vehicleUrl',
            'driver_url' => 'driverUrl',
        ];
    }
}
