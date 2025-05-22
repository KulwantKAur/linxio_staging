<?php

namespace App\Service\EventLog\Mapper;

use App\Entity\Notification\Event;
use App\Entity\User;

class UserEventLog extends EventLog
{
    protected $context;

    /**
     * UserEventLog constructor.
     * @param User $entity
     * @param User|null $currentUser
     * @param Event $event
     * @param array $context
     */
    public function __construct(User $entity, ?User $currentUser, Event $event, array $context = [])
    {
        $this->entity = $entity;
        $this->currentUser = $currentUser;
        $this->currentEvent = $event;
        $this->context = $context;
    }

    /**
     * @return array
     * @throws \Exception
     */
    public function getDetails()
    {
        $context['context'] = $this->context ?? null;
        $entityDetails = $this->entity->toArray();

        return array_merge($entityDetails, $context);
    }

    /**
     * @return string
     */
    public function getTeamData()
    {
        $teamData = ($this->entity->isInClientTeam())
            ? $this->entity->getClient()->getName()
            : $this->entity->getTeamType();

        return $teamData;
    }

    /**
     * @return mixed
     */
    public function getEventSource()
    {
        switch ($this->currentEvent->getName()) {
            case Event::USER_CHANGED_SURNAME:
                return $this->entity->getSurname();
            case Event::USER_CHANGED_NAME:
                return $this->entity->getName();
            default:
                return $this->entity->getEmail();
        }
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
