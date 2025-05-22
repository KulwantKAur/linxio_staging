<?php

namespace App\Service\EventLog\Report\ReportBuilder\EntityHandler;

use App\Entity\EventLog\EventLog;
use App\Entity\Notification\Event;
use App\Entity\Notification\Notification;
use App\Service\EventLog\Report\ReportBuilder\AbstractEntityHandler;
use App\Util\DateHelper;
use App\Util\MetricHelper;

class TrackerHistoryEntityHandler extends AbstractEntityHandler
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

        if (array_key_exists(EventLog::AREAS, $fields)) {
            $areas = $eventLog->getDetails()['device']['deviceInstallation']['vehicle'][EventLog::AREAS] ?? null;

            if ($areas) {
                $data[$fields[EventLog::AREAS]] = implode(
                    ', ',
                    array_map(
                        function ($areas) {
                            return $areas['area']['name'] ?? null;
                        },
                        $areas
                    )
                );
            } else {
                $data[$fields[EventLog::AREAS]] = null;
            }
        }

        if (array_key_exists(EventLog::ADDRESS, $fields)) {
            $data[$fields[EventLog::ADDRESS]] =
                $eventLog->getDetails()['context']['address'] ??
                $eventLog->getDetails()['device']['trackerData'][EventLog::ADDRESS] ?? null;
        }

        if (array_key_exists(EventLog::MAX_SPEED, $fields)) {
            $data[$fields[EventLog::MAX_SPEED]] =
                MetricHelper::speedToHumanKmH($eventLog->getDetails()[EventLog::SPEED] ?? null)
                ?? MetricHelper::speedToHumanKmH(
                $eventLog->getDetails()['context'][EventLog::SPEED] ?? null
            );
        }

        if (array_key_exists(EventLog::SPEED_LIMIT, $fields)) {
            $data[$fields[EventLog::SPEED_LIMIT]] =
                MetricHelper::speedToHumanKmH($eventLog->getDetails()[EventLog::SPEED_LIMIT] ?? null)
                ?? MetricHelper::speedToHumanKmH(
                $eventLog->getDetails()['context'][EventLog::SPEED_LIMIT] ?? null
            );
        }

        if (array_key_exists(EventLog::SPEED, $fields)) {
            $data[$fields[EventLog::SPEED]] =
                MetricHelper::speedToHumanKmH($eventLog->getDetails()[EventLog::SPEED] ?? null)
                ?? MetricHelper::speedToHumanKmH(
                $eventLog->getDetails()['context'][EventLog::SPEED] ?? null
            );
        }

        if (array_key_exists(EventLog::DURATION, $fields)) {
            $data[$fields[EventLog::DURATION]] = DateHelper::seconds2human(
                $eventLog->getDetails()['context'][EventLog::DURATION] ?? null
            );
        }

        if (array_key_exists(EventLog::DISTANCE, $fields)) {
            $data[$fields[EventLog::DISTANCE]] = MetricHelper::metersToHumanKm(
                $eventLog->getDetails()['context'][EventLog::DISTANCE] ?? null
            );
        }

        if (array_key_exists(EventLog::DEVICE_VOLTAGE, $fields)) {
            $data[$fields[EventLog::DEVICE_VOLTAGE]] = $eventLog->getDetails()['externalVoltage']
                ? $eventLog->getDetails()['externalVoltage'] / 1000
                : null;
        }

        if (array_key_exists(EventLog::DEVICE_BATTERY_PERCENTAGE, $fields)) {
            $data[$fields[EventLog::DEVICE_BATTERY_PERCENTAGE]] =
                $eventLog->getDetails()['batteryVoltagePercentage'] ?? null;
        }

        if (array_key_exists(EventLog::DEVICE_IMEI, $fields)) {
            $data[$fields[EventLog::DEVICE_IMEI]] =
                $eventLog->getDetails()['device']['imei'] ?? null;
        }

        if (array_key_exists(EventLog::SHORT_DETAILS, $fields)) {
            $data[$fields[EventLog::SHORT_DETAILS]] = [
//                EventLog::LAST_COORDINATES =>
//                    $eventLog->getDetails()[EventLog::LAST_COORDINATES] ?? null,
//                EventLog::DEVICE_IMEI => $eventLog->getDetails()['device']['imei'] ?? null,
//                EventLog::VEHICLE_REG_NO =>
//                    $eventLog->getDetails()['device']['deviceInstallation']['vehicle']['regNo'] ?? null,
//                EventLog::ADDRESS => isset($fields[EventLog::ADDRESS]) ? $data[$fields[EventLog::ADDRESS]] : null,
//                EventLog::ADDRESS => $eventLog->getDetails()['context']['address'] ?? $eventLog->getDetails()['device']['trackerData'][EventLog::ADDRESS] ?? null,
//                EventLog::AREAS => isset($fields[EventLog::AREAS]) ? $data[$fields[EventLog::AREAS]] : null,
//                EventLog::LIMIT => isset($fields[EventLog::LIMIT]) ? $data[$fields[EventLog::LIMIT]] : null,
            ];

            $data[$fields[EventLog::SHORT_DETAILS]] =
                ($data[$fields[EventLog::SHORT_DETAILS]])
                    ? str_replace('&', ', ', urldecode(http_build_query($data[$fields[EventLog::SHORT_DETAILS]])))
                    : null;
        }

        if ($eventLog->getEvent()->getName() === Event::EXCEEDING_SPEED_LIMIT) {
            $speed = $eventLog->getDetails()[EventLog::SPEED] ?? $eventLog->getDetails()['context'][EventLog::SPEED_LIMIT] ?? null;
            $speedLimit = $eventLog->getDetails()['context'][EventLog::SPEED_LIMIT] ?? null;

            $data[$fields[EventLog::VEHICLE_DEFAULT_LABEL]] = $eventLog->getDetails()['device']['deviceInstallation']['vehicle']['defaultLabel'] ?? null;

            if ($speed && $speedLimit) {
                $data[$fields[EventLog::SPEED_OVER_LIMIT_PERCENT]] = round((1 - $speedLimit / $speed) * 100);
            } else {
                $data[$fields[EventLog::SPEED_OVER_LIMIT_PERCENT]] = null;
            }
        }

        return array_merge(array_flip($this->getHeader()), $data);
    }
}
