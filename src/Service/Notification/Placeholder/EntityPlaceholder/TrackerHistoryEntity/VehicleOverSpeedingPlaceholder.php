<?php

namespace App\Service\Notification\Placeholder\EntityPlaceholder\TrackerHistoryEntity;

use App\Service\Notification\Placeholder\AbstractEntityPlaceholder;

class VehicleOverSpeedingPlaceholder extends AbstractEntityPlaceholder
{
//    public function getPlaceholder(): array
//    {
//        return [
//            Event::VEHICLE_OVERSPEEDING => [
//                Event::TYPE_USER => function (TrackerHistory $entity) use ($event) {
//                    return [
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
//                        'status' => $entity->getVehicle()->getStatus(),
//                        'avg_speed' => $entity->getSpeed(),
//                        'duration' => ' - ',
//                        'triggered_by' => $entity->getVehicle()->getDriverName() ?? self::DEFAULT_UNKNOWN,
//                        'event_time' => $entity->getTs()
//                            ? DateHelper::formatDate(
//                                $entity->getTs(),
//                                DateHelper::FORMAT_DATE_SHORT_TIME,
//                                $entity->getTimeZoneName()
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
            'reg_no' => 'regNo',
            'model' => 'model',
            'team' => 'team',
            'driver' => 'driver',
            'status' => 'status',
            'avg_speed' => 'avgSpeed',
            'duration' => 'duration',
            'triggered_by' => 'triggeredBy',
            'event_time' => 'tsTime',
            'reg_no_with_model' => 'regNoWithModel',
            'vehicle_url' => 'vehicleUrl',
            'driver_url' => 'driverUrl',
        ];
    }
}
