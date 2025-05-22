<?php

namespace App\Service\EventLog\Report\ReportBuilder\EntityHandler;

use App\Entity\EventLog\EventLog;
use App\Entity\Notification\Event;
use App\Service\EventLog\Report\ReportBuilder\AbstractEntityHandler;
use App\Util\DateHelper;

class ServiceRecordEntityHandler extends AbstractEntityHandler
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

        if (array_key_exists(EventLog::VEHICLE_REG_NO, $fields)) {
            switch ($this->getEvent()->getName()) {
                case Event::SERVICE_RECORD_ADDED:
                    $data[$fields[EventLog::VEHICLE_REG_NO]] = $eventLog->getDetails()['vehicleRegNo'] ?? null;
                    break;
                case Event::SERVICE_REPAIR_ADDED:
                    $data[$fields[EventLog::VEHICLE_REG_NO]] = $eventLog->getDetails()['repairVehicle']['regNo'] ?? null;
                    break;
            }
        }

        return array_merge(array_flip($this->getHeader()), $data);
    }
}
