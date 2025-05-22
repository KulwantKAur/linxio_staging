<?php

namespace App\Service\EventLog\Report\ReportBuilder\EntityHandler;

use App\Entity\EventLog\EventLog;
use App\Service\EventLog\Report\ReportBuilder\AbstractEntityHandler;
use App\Util\DateHelper;
use Carbon\Carbon;

class VehicleEntityHandler extends AbstractEntityHandler
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
        $details = $eventLog->getDetails();

        if (array_key_exists(EventLog::OLD_VALUE, $fields)) {
            $data[$fields[EventLog::OLD_VALUE]] = $details['context']['oldValue'] ?? null;
        }

        if (array_key_exists(EventLog::NEW_VALUE, $fields)) {
            $data[$fields[EventLog::NEW_VALUE]] = !empty($details['driver'])
                ? ($details['driver']['name'] ?? null)
                . ' ' . ($details['driver']['surname'] ?? null)
                : null;
        }

        if (array_key_exists(EventLog::DURATION, $fields)) {
            $data[$fields[EventLog::DURATION]] = $details['context'][EventLog::DURATION] ??
                (isset($details['context']['lastCoordinates']['ts']) && isset($details['context']['gpsStatusDurationSetting'])
                    ? (new Carbon())->diffInSeconds($details['context']['lastCoordinates']['ts']): null);
            if ($data[$fields[EventLog::DURATION]] ?? null) {
                $data[$fields[EventLog::DURATION]] = DateHelper::seconds2human($data[$fields[EventLog::DURATION]]);
            }
        }

        return array_merge(array_flip($this->getHeader()), $data);
    }
}
