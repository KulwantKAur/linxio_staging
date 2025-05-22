<?php

namespace App\Service\EventLog\Mapper;

use App\Entity\Device;
use App\Entity\Notification\Event;
use App\Entity\User;

class DeviceEventLog extends EventLog
{

    /**
     * DeviceEventLog constructor.
     * @param Device $entity
     * @param User|null $currentUser
     * @param Event $event
     */
    public function __construct(Device $entity, ?User $currentUser, Event $event)
    {
        $this->entity = $entity;
        $this->currentUser = $currentUser;
        $this->currentEvent = $event;
    }

    /**
     * @return string
     */
    public function getTeamData()
    {
        $teamData = ($this->entity->getClient())
            ? $this->entity->getClient()
            : $this->entity->getTeam()->getType();

        return $teamData;
    }

    /**
     * @return string
     */
    public function getEventSource()
    {
        return $this->entity->getImei();
    }

    /**
     * @return int|null
     */
    public function getVehicleId()
    {
        return $this->entity->getVehicle() ? $this->entity->getVehicle()->getId() : null;
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
     */
    public function getEntityTeamId(): ?int
    {
        return $this->entity?->getTeam()->getId();
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
