<?php

namespace App\Service\EventLog\Report\ReportBuilder\EntityHandler;

use App\Entity\EventLog\EventLog;
use App\Entity\Notification\Notification;
use App\Service\EventLog\Report\ReportBuilder\AbstractEntityHandler;
use App\Util\DateHelper;
use App\Util\MetricHelper;

class AreaHistoryEntityHandler extends AbstractEntityHandler
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
            $areas = $eventLog->getDetails()['context'][EventLog::AREAS]
                ?? $eventLog->getDetails()['vehicle'][EventLog::AREAS] ?? null;

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

        if (array_key_exists(EventLog::MAX_SPEED, $fields)) {
            $data[$fields[EventLog::MAX_SPEED]] =
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
                $eventLog->getDetails()[EventLog::DISTANCE] ?? null
            );
        }

        if (array_key_exists(EventLog::SHORT_DETAILS, $fields)) {
            $data[$fields[EventLog::SHORT_DETAILS]] = [
//                EventLog::LAST_COORDINATES =>
//                    $eventLog->getDetails()['device']['trackerData'][EventLog::LAST_COORDINATES] ?? null,
                EventLog::DEVICE_IMEI => $eventLog->getDetails()['device']['imei'] ?? null,
                EventLog::VEHICLE_REG_NO => $eventLog->getDetails()['vehicle']['regNo'] ?? null,
                EventLog::ADDRESS => $eventLog->getDetails()['device']['trackerData'][EventLog::ADDRESS] ?? null,
                EventLog::AREAS => $data[$fields[EventLog::AREAS]] ?? null,
            ];
            $data[$fields[EventLog::SHORT_DETAILS]] =
                ($data[$fields[EventLog::SHORT_DETAILS]])
                ? str_replace('&', ', ', urldecode(http_build_query($data[$fields[EventLog::SHORT_DETAILS]])))
                : null;
        }

        return array_merge(array_flip($this->getHeader()), $data);
    }
}
