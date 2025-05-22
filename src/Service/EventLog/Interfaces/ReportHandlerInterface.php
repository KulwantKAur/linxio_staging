<?php

namespace App\Service\EventLog\Interfaces;

use App\Entity\EventLog\EventLog;
use App\Entity\Notification\Event;

interface ReportHandlerInterface
{
    /**
     * @return ?Event
     */
    public function getEvent(): ?Event;

    /**
     * @return array
     */
    public function getTeamNotificationByEvent(): array;

    /**
     * @return array
     */
    public function getHeader(): array;

    /**
     * @param EventLog $eventLog
     * @param array $include
     * @return array
     */
    public function toExport(EventLog $eventLog, array $include = []): array;
}
