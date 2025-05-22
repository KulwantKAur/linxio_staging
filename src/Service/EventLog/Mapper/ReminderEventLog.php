<?php

namespace App\Service\EventLog\Mapper;

use App\Entity\Notification\Event;
use App\Entity\Reminder;
use App\Entity\User;
use App\Util\StringHelper;

class ReminderEventLog extends EventLog
{

    /**
     * ReminderEventLog constructor.
     * @param Reminder $entity
     * @param User|null $currentUser
     * @param Event $event
     */
    public function __construct(Reminder $entity, ?User $currentUser, Event $event)
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
        return $this->entity->getEntity()->getClient()
            ? $this->entity->getEntity()->getClientName()
            : $this->entity->getEntity()->getTeam()->getType();
    }

    /**
     * @return string
     */
    public function getEventSource()
    {
        return $this->entity->getTitle();
    }

    /**
     * @return string
     */
    public function getEventSourceType()
    {
        return StringHelper::getClassName(Event::ENTITY_TYPE_VEHICLE);
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
     */
    public function getEntityTeamId(): ?int
    {

        return $this->entity?->getTeamId();
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
