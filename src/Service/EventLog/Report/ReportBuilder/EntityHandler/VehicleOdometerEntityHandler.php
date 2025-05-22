<?php

namespace App\Service\EventLog\Report\ReportBuilder\EntityHandler;

use App\Entity\EventLog\EventLog;
use App\Service\EventLog\Report\ReportBuilder\AbstractEntityHandler;
use App\Util\DateHelper;

class VehicleOdometerEntityHandler extends AbstractEntityHandler
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

        if (array_key_exists(EventLog::OLD_VALUE, $fields)) {
            $data[$fields[EventLog::OLD_VALUE]] = !empty($eventLog->getDetails()['context']['oldValue'])
                ? (int)($eventLog->getDetails()['context']['oldValue'] / 1000)
                : null;
        }

        if (array_key_exists(EventLog::NEW_VALUE, $fields)) {
            $data[$fields[EventLog::NEW_VALUE]] = !empty($eventLog->getDetails()['odometer'])
                ? (int)($eventLog->getDetails()['odometer'] / 1000)
                : null;
        }

        return array_merge(array_flip($this->getHeader()), $data);
    }
}
