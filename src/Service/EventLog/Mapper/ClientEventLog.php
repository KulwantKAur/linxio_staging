<?php

namespace App\Service\EventLog\Mapper;

use App\Entity\Client;
use App\Entity\Notification\Event;
use App\Entity\User;
use App\Util\StringHelper;

class ClientEventLog extends EventLog
{

    /**
     * ClientEventLog constructor.
     * @param Client $entity
     * @param User|null $currentUser
     * @param Event $event
     */
    public function __construct(Client $entity, ?User $currentUser, Event $event)
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
        $teamData = ($this->entity->getTeam())
            ? $this->entity->getName()
            : $this->entity->getTeam()->getType();

        return $teamData;
    }

    /**
     * @return string
     */
    public function getEventSource()
    {
        return $this->entity->getName();
    }

    /**
     * @return string
     */
    public function getEventSourceType()
    {
        return StringHelper::getClassName(Event::ENTITY_TYPE_USER);
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
