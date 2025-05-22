<?php

namespace App\Service\EventLog\Report\ReportBuilder\EntityHandler;

use App\Entity\EventLog\EventLog;
use App\Entity\Team;
use App\Service\EventLog\Report\ReportBuilder\AbstractEntityHandler;
use App\Util\DateHelper;

class UserEntityHandler extends AbstractEntityHandler
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
            $data[$fields[EventLog::OLD_VALUE]] = $eventLog->getDetails()['context']['oldValue'] ?? null;
        }

        if (array_key_exists(EventLog::EVENT_TEAM, $fields)) {
            if ($eventLog->getDetails()['team']['type'] === Team::TEAM_RESELLER) {
                $data[$fields[EventLog::EVENT_TEAM]] = $eventLog->getDetails()['team']['resellerName']
                    ?? $eventLog->getEventTeam() ?? null;
            }
        }

        return array_merge(array_flip($this->getHeader()), $data);
    }
}
