<?php

namespace App\Service\EventLog\Report\ReportBuilder\EntityHandler;

use App\Entity\EventLog\EventLog;
use App\Service\EventLog\Report\ReportBuilder\AbstractEntityHandler;
use App\Util\DateHelper;

class DigitalFormAnswerEntityHandler extends AbstractEntityHandler
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

        if (array_key_exists(EventLog::USER, $fields)) {
            $data[$fields[EventLog::USER]] = $eventLog->getDetails()['user']['email'] ?? null;
        }

        if (array_key_exists(EventLog::VEHICLE_REG_NO, $fields)) {
            $data[$fields[EventLog::VEHICLE_REG_NO]] = $eventLog->getDetails()['vehicle']['regNo'] ?? null;
        }

        return array_merge(array_flip($this->getHeader()), $data);
    }
}
