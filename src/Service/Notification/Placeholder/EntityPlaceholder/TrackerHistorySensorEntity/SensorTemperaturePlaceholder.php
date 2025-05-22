<?php

namespace App\Service\Notification\Placeholder\EntityPlaceholder\TrackerHistorySensorEntity;

use App\Service\Notification\Placeholder\AbstractEntityPlaceholder;

class SensorTemperaturePlaceholder extends AbstractEntityPlaceholder
{
//    public function getPlaceholder(): array
//    {
//        return [
//            Event::SENSOR_TEMPERATURE => [
//                Event::TYPE_USER => static function (TrackerHistorySensor $entity) use ($context) {
//                    return [
//                        'reg_no' => $entity->getVehicle()
//                            ? $entity->getVehicle()->getRegNo() : self::DEFAULT_UNKNOWN,
//                        'sensor_id' => $entity->getDeviceSensor() ? $entity->getDeviceSensor()->getSensorIdField()
//                            : self::DEFAULT_UNKNOWN,
//                        'label' => $entity->getDeviceSensor() && $entity->getDeviceSensor()->getLabel()
//                            ? '(' . $entity->getDeviceSensor()->getLabel() . ')'
//                            : null,
//                        'event_time' => DateHelper::formatDate(
//                            $entity->getOccurredAt(),
//                            DateHelper::FORMAT_DATE_SHORT_TIME,
//                            $entity->getVehicle() ? $entity->getVehicle()->getTimeZoneName() : null
//                        ),
//                        'sensor_temperature' => $entity->getTemperature() ?? self::DEFAULT_UNKNOWN
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
            'sensor_id' => 'sensorId',
            'label' => 'sensorLabel',
            'event_time' => 'occurredAtTime',
            'sensor_temperature' => 'sensorTemperature',
        ];
    }
}
