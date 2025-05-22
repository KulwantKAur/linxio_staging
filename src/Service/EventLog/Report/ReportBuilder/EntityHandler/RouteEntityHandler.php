<?php

namespace App\Service\EventLog\Report\ReportBuilder\EntityHandler;

use App\Entity\EventLog\EventLog;
use App\Entity\Notification\Notification;
use App\Service\EventLog\Report\ReportBuilder\AbstractEntityHandler;
use App\Util\DateHelper;

class RouteEntityHandler extends AbstractEntityHandler
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

        if (array_key_exists(EventLog::DURATION, $fields)) {
            $data[$fields[EventLog::DURATION]] = $eventLog->getDetails()[EventLog::DURATION] ?? null;
        }

        if (array_key_exists(EventLog::DISTANCE, $fields)) {
            $data[$fields[EventLog::DISTANCE]] = $eventLog->getDetails()[EventLog::DISTANCE] ?? null;
        }

        if (array_key_exists(EventLog::FORM, $fields) && isset($eventLog->getDetails()['context'][EventLog::FORM])) {
            $data[$fields[EventLog::FORM]] = reset($eventLog->getDetails()['context'][EventLog::FORM]);
        }

        if (array_key_exists(EventLog::SHORT_DETAILS, $fields)) {
            $data[$fields[EventLog::SHORT_DETAILS]] = [
//                EventLog::LAST_COORDINATES =>
//                    $eventLog->getDetails()['pointFinish'][EventLog::LAST_COORDINATES] ?? null,
                EventLog::DEVICE_IMEI => $eventLog->getDetails()['device']['imei'] ?? null,
                EventLog::VEHICLE_REG_NO => $eventLog->getDetails()['vehicle']['regNo'] ?? null,
                EventLog::ADDRESS => $eventLog->getDetails()[EventLog::ADDRESS] ?? null,
                EventLog::DURATION => $data[$fields[EventLog::DURATION]] ?? null,
                EventLog::DISTANCE => $data[$fields[EventLog::DISTANCE]] ?? null,
                EventLog::LIMIT => $data[$fields[EventLog::LIMIT]] ?? null,
            ];
            $data[$fields[EventLog::SHORT_DETAILS]] =
                ($data[$fields[EventLog::SHORT_DETAILS]])
                    ? str_replace('&', ', ', urldecode(http_build_query($data[$fields[EventLog::SHORT_DETAILS]])))
                    : null;
        }

        return array_merge(array_flip($this->getHeader()), $data);
    }
}
