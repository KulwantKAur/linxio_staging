<?php

namespace App\Service\EventLog\Report\ReportBuilder\EntityHandler;

use App\Entity\EventLog\EventLog;
use App\Service\EventLog\Report\ReportBuilder\AbstractEntityHandler;
use App\Util\DateHelper;

class DocumentRecordEntityHandler extends AbstractEntityHandler
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

        if (array_key_exists(EventLog::TITLE, $fields)) {
            $data[$fields[EventLog::TITLE]] = $eventLog->getDetails()['document']['title'] ?? null;
        }

        if (array_key_exists(EventLog::EXPIRED_DATE, $fields)) {
            $data[$fields[EventLog::EXPIRED_DATE]] = DateHelper::formatDate(
                $eventLog->getDetails()['expDate'],
                DateHelper::FORMAT_DATE_SHORT_TIME,
                $this->getUser()->getTimezone()
            ) ?? null;
        }

        return array_merge(array_flip($this->getHeader()), $data);
    }
}
