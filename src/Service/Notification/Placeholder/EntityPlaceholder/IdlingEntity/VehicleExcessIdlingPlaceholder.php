<?php

namespace App\Service\Notification\Placeholder\EntityPlaceholder\IdlingEntity;

use App\Service\Notification\Placeholder\AbstractEntityPlaceholder;

class VehicleExcessIdlingPlaceholder extends AbstractEntityPlaceholder
{
//    public function getPlaceholder(): array
//    {
//        return [
//            Event::VEHICLE_EXCESSING_IDLING => [
//                Event::TYPE_USER => function (Idling $entity) use ($event) {
//                    return [
//                        'reg_no' => $entity->getVehicle() ? $entity->getVehicle()->getRegNo() : null,
//                        'event_time' => $entity->getStartedAt()
//                            ? DateHelper::formatDate(
//                                $entity->getStartedAt(),
//                                DateHelper::FORMAT_DATE_SHORT_TIME,
//                                $entity->getTimeZoneName()
//                            ) : null,
//                        'duration' =>
//                            $entity->getDuration() ? DateHelper::seconds2human($entity->getDuration()) : null,
//                        'reg_no_with_model' => $entity->getVehicle()
//                            ? !empty($entity->getVehicle()->getModel())
//                                ? vsprintf(
//                                    '%s (%s)',
//                                    [$entity->getVehicle()->getRegNo(), $entity->getVehicle()->getModel()]
//                                )
//                                : vsprintf('%s', [$entity->getVehicle()->getRegNo()])
//                            : self::DEFAULT_UNKNOWN,
//                        'driver' => $entity->getVehicle()
//                            ? ($entity->getVehicle()->getDriverName() ?? self::DEFAULT_UNKNOWN)
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
//            'reg_no' => 'regNo',
            'reg_no_or_device' => 'regNoOrDevice',
            'event_time' => 'startedTime',
            'duration' => 'duration',
//            'reg_no_with_model' => 'regNoWithModel',
            'reg_no_with_model_or_device' => 'regNoWithModelOrDevice',
            'driver' => 'driver',
            'note' => 'note',
            'vehicle_url' => 'vehicleUrl',
            'driver_url' => 'driverUrl',
        ];
    }
}
