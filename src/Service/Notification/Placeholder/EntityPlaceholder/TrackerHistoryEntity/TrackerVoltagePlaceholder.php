<?php

namespace App\Service\Notification\Placeholder\EntityPlaceholder\TrackerHistoryEntity;

use App\Service\Notification\Placeholder\AbstractEntityPlaceholder;

class TrackerVoltagePlaceholder extends AbstractEntityPlaceholder
{
//    public function getPlaceholder(): array
//    {
//        return [
//            Event::TRACKER_VOLTAGE => [
//                Event::TYPE_USER => function (TrackerHistory $entity) use ($event) {
//                    return [
//                        'device' => $entity->getDevice() ? $entity->getDevice()->getImei() : null,
//                        'battery_voltage' => $entity->getExternalVoltageVolts(),
//                        'vehicle' => $entity->getVehicle()
//                            ? $entity->getVehicle()->getRegNo() : self::DEFAULT_UNKNOWN,
//                        'model' => $entity->getVehicle()
//                            ? $entity->getVehicle()->getModel() : self::DEFAULT_UNKNOWN,
//                        'driver' => $entity->getVehicle()
//                            ? ($entity->getVehicle()->getDriverName() ?? self::DEFAULT_UNKNOWN)
//                            : self::DEFAULT_UNKNOWN,
//                        'event_time' => $entity->getCreatedAt()
//                            ? DateHelper::formatDate(
//                                $entity->getCreatedAt(),
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
            'device' => 'device',
            'battery_voltage' => 'batteryVoltage',
            'vehicle' => 'regNo',
            'model' => 'model',
            'driver' => 'driver',
            'event_time' => 'createdTime',
            'reg_no_with_model' => 'regNoWithModel',
            'vehicle_url' => 'vehicleUrl',
            'driver_url' => 'driverUrl',
        ];
    }
}
