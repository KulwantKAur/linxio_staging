<?php

namespace App\Service\EventLog\Manager;

use App\Entity\EventLog\EventLog;
use App\Entity\Team;

class EventLogManager
{

    public function create(
        array $details,
        string $triggeredDetails,
        array $triggeredByDetails,
        string $eventSourceType,
        array $eventDetails,
        ?object $createdBy,
        object $event,
        ?\DateTime $eventDate,
        ?Team $team,
        ?int $vehicleId,
        ?int $deviceId,
        ?int $driverId,
        ?array $shortDetails,
        ?int $entityId,
        ?int $entityTeamId,
        ?int $teamBy,
        ?int $userBy
    ): EventLog {
        return (new EventLog())
            ->setDetails($details)
            ->setDetailId(null)
            ->setTriggeredDetails($triggeredDetails)
            ->setTriggeredByDetails($triggeredByDetails)
            ->setEventSourceType($eventSourceType)
            ->setEventDetails($eventDetails)
            ->setCreatedBy($createdBy)
            ->setEvent($event)
            ->setEventDate($eventDate)
            ->setTeam($team)
            ->setVehicleId($vehicleId)
            ->setDeviceId($deviceId)
            ->setDriverId($driverId)
            ->setShortDetails($shortDetails)
            ->setEntityId($entityId)
            ->setEntityTeamId($entityTeamId)
            ->setTeamBy($teamBy)
            ->setUserBy($userBy);
    }
}
