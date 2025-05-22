<?php

namespace App\Service\EventLog\Report\ReportBuilder\EntityHandler;

use App\Entity\EventLog\EventLog;
use App\Entity\Tracker\TrackerIOType;
use App\Service\EventLog\Report\ReportBuilder\AbstractEntityHandler;
use App\Util\DateHelper;

class TrackerHistoryIOEntityHandler extends AbstractEntityHandler
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

        if (array_key_exists(EventLog::SENSOR_STATUS, $fields)) {
            $data[$fields[EventLog::SENSOR_STATUS]] = isset($eventLog->getDetails()['statusIO'])
                ? (($eventLog->getDetails()['statusIO']) ? 'online' : 'offline')
                : null;
        }

        if (array_key_exists(EventLog::SENSOR_IO_TYPE, $fields)) {
            $data[$fields[EventLog::SENSOR_IO_TYPE]] = !empty($eventLog->getDetails()['sensorIOTypeId'])
                ? $this->getNameIOType($this->getDigitalIOTypes(), $eventLog->getDetails()['sensorIOTypeId'])
                : null;
        }

        if (array_key_exists(EventLog::SHORT_DETAILS, $fields)) {
            $data[$fields[EventLog::SHORT_DETAILS]] = [
//                EventLog::LAST_COORDINATES =>
//                    $eventLog->getDetails()['device']['trackerData'][EventLog::LAST_COORDINATES] ?? null,
                EventLog::DEVICE_IMEI => $eventLog->getDetails()['device']['imei'] ?? null,
                EventLog::VEHICLE_REG_NO =>
                    $eventLog->getDetails()['device']['deviceInstallation']['vehicle']['regNo'] ?? null,
                EventLog::ADDRESS => $eventLog->getDetails()['device']['trackerData'][EventLog::ADDRESS] ?? null,

                EventLog::AREAS => implode(
                    ', ',
                    array_map(
                        function ($areas) {
                            return $areas['area']['name'] ?? null;
                        },
                        $eventLog->getDetails()['device']['deviceInstallation']['vehicle'][EventLog::AREAS]
                    )
                ),
                EventLog::SENSOR_STATUS => $data[$fields[EventLog::SENSOR_STATUS]] ?? null,
                EventLog::SENSOR_IO_TYPE => $data[$fields[EventLog::SENSOR_IO_TYPE]] ?? null,
            ];
            $data[$fields[EventLog::SHORT_DETAILS]] =
                ($data[$fields[EventLog::SHORT_DETAILS]])
                ? str_replace('&', ', ', urldecode(http_build_query($data[$fields[EventLog::SHORT_DETAILS]])))
                : null;
        }

        return array_merge(array_flip($this->getHeader()), $data);
    }
}
