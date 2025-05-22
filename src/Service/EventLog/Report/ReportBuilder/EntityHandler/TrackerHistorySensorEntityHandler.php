<?php

namespace App\Service\EventLog\Report\ReportBuilder\EntityHandler;

use App\Entity\EventLog\EventLog;
use App\Entity\Notification\Notification;
use App\Entity\Sensor;
use App\Service\EventLog\Report\ReportBuilder\AbstractEntityHandler;
use App\Util\DateHelper;

class TrackerHistorySensorEntityHandler extends AbstractEntityHandler
{

    /**
     * @param EventLog $eventLog
     * @param array $fields
     * @return array
     * @throws \Exception
     */
    public function toExport(EventLog $eventLog, array $fields = []): array
    {
        $data = parent::toExport($eventLog, $fields);

        if (array_key_exists(EventLog::SENSOR_TEMPERATURE, $fields)) {
            $data[$fields[EventLog::SENSOR_TEMPERATURE]] = $eventLog->getDetails()['temperature'] ?? null;
        }

        if (array_key_exists(EventLog::SENSOR_HUMIDITY, $fields)) {
            $data[$fields[EventLog::SENSOR_HUMIDITY]] = $eventLog->getDetails()['humidity'] ?? null;
        }

        if (array_key_exists(EventLog::SENSOR_LIGHT, $fields)) {
            $data[$fields[EventLog::SENSOR_LIGHT]] = isset($eventLog->getDetails()['light'])
                ? ($eventLog->getDetails()['light'] ? Sensor::LIGHT_ON : Sensor::LIGHT_OFF)
                : null;
        }

        if (array_key_exists(EventLog::SENSOR_BATTERY_LEVEL, $fields)) {
            $data[$fields[EventLog::SENSOR_BATTERY_LEVEL]] = $eventLog->getDetails()['batteryPercentage'] ?? null;
        }

        if (array_key_exists(EventLog::SENSOR_STATUS, $fields)) {
            $data[$fields[EventLog::SENSOR_STATUS]] = $eventLog->getDetails()['deviceSensorStatus'] ?? null;
        }

        return array_merge(array_flip($this->getHeader()), $data);
    }
}
