<?php

namespace App\Service\Notification\Placeholder\EntityPlaceholder\AreaHistoryEntity;

use App\Service\Notification\Placeholder\AbstractEntityPlaceholder;

class VehicleLeftAreaPlaceholder extends AbstractEntityPlaceholder
{
//    public function getPlaceholder(): array
//    {
//        return [
//            Event::VEHICLE_GEOFENCE_LEAVE => [
//                Event::TYPE_USER => function (AreaHistory $entity) use ($event) {
//                    return [
//                        'area' => $entity->getArea()->getName(),
//                        'reg_no' => $entity->getVehicle()->getRegNo(),
//                        'team' => $entity->getVehicle()
//                            ? $entity->getVehicle()->getTeam()->isClientTeam()
//                                ? $entity->getVehicle()->getTeam()->getClientName()
//                                : $entity->getVehicle()->getTeam()->getType()
//                            : self::DEFAULT_UNKNOWN,
//                        'model' => $entity->getVehicle()
//                            ? $entity->getVehicle()->getModel() : self::DEFAULT_UNKNOWN,
//                        'driver' => $entity->getVehicle()->getDriverName()
//                            ? $entity->getVehicle()->getDriverName()
//                            : null,
//                        'event_time' => $entity->getDeparted()
//                            ? DateHelper::formatDate(
//                                $entity->getDeparted(),
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
            'team' => 'team',
            'model' => 'model',
            'driver' => 'driver',
            'event_time' => 'departedTime',
            'reg_no_with_model' => 'regNoWithModel',
            'vehicle_url' => 'vehicleUrl',
            'driver_url' => 'driverUrl',
        ];
    }
}
