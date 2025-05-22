<?php

namespace App\Service\EventLog\Report\ReportBuilder\EntityHandler;

use App\Entity\EventLog\EventLog;
use App\Service\EventLog\Report\ReportBuilder\AbstractEntityHandler;
use App\Util\DateHelper;

class TrackerAuthUnknownEntityHandler extends AbstractEntityHandler
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

        return array_merge(array_flip($this->getHeader()), $data);
    }
}
