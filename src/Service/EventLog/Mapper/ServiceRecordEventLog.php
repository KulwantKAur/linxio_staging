<?php

namespace App\Service\EventLog\Mapper;

use App\Entity\Notification\Event;
use App\Entity\Reminder;
use App\Entity\ServiceRecord;
use App\Entity\User;

class ServiceRecordEventLog extends EventLog
{

    /**
     * ServiceRecordEventLog constructor.
     * @param ServiceRecord $entity
     * @param User|null $currentUser
     * @param Event $event
     */
    public function __construct(ServiceRecord $entity, ?User $currentUser, Event $event)
    {
        $this->entity = $entity;
        $this->currentUser = $currentUser;
        $this->currentEvent = $event;
    }

    /**
     * @return array
     * @throws \Exception
     */
    public function getDetails()
    {
        $team['team'] = ($this->entity->getTeam()) ? $this->entity->getTeam()->toArray() : null;
        $entityDetails = $this->entity->getReminder()
            ? $this->entity->getReminder()->toArray(Reminder::REPORT_DISPLAY_VALUES)
            : $this->entity->toArray();

        return array_merge($entityDetails, $team);
    }

    /**
     * @return mixed|string|null
     * @throws \Exception
     */
    public function getTeamData()
    {
        $teamData = ($this->entity->getTeam())
            ? $this->entity->getTeam()->getClientName()
            : $this->entity->getTeam()->getType();

        return $teamData;
    }


    /**
     * @return string
     * @throws \Exception
     */
    public function getEventSource()
    {
        return $this->entity->getTitle() ? $this->entity->getTitle() : EventLog::DEFAULT_TRIGGERED_UNKNOWN;
    }

    /**
     * @return int|null
     */
    public function getVehicleId()
    {
        return $this->entity?->getVehicle()?->getId();
    }

    /**
     * @return null
     */
    public function getEntityId()
    {
        return $this->entity->getId();
    }

    /**
     * @return int|null
     * @throws \Exception
     */
    public function getEntityTeamId(): ?int
    {
        return $this->entity?->getTeam()?->getId();
    }

    /**
     * Team, who changed this user
     * @return int|null
     */
    public function getTeamBy(): ?int
    {
        return $this->currentUser?->getTeam()->getId();
    }

    /**
     * User id, who changed this user
     * @return int|null
     */
    public function getUserBy(): ?int
    {
        return $this->currentUser?->getId();
    }
}
