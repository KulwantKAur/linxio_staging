<?php

namespace App\Service\EventLog\Mapper;

use App\Entity\Asset;
use App\Entity\Notification\Event;
use App\Entity\User;

class AssetEventLog extends EventLog
{
    protected $context;

    /**
     * VehicleEventLog constructor.
     * @param Asset $entity
     * @param User|null $currentUser
     * @param Event $event
     * @param array $context
     */
    public function __construct(Asset $entity, ?User $currentUser, Event $event, $context = [])
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
        $entityDetails = $this->entity->toArray(array_merge(Asset::DEFAULT_DISPLAY_VALUES, ['lastOccurredAt']));

        return array_merge($entityDetails, $context);
    }

    /**
     * @return string
     */
    public function getTeamData()
    {
        return $this->entity->getClient() ? $this->entity->getClientName() : $this->entity->getTeam()->getType();
    }

    /**
     * @return string
     */
    public function getEventSource()
    {
        return $this->entity->getName();
    }

    /**
     * @return mixed
     */
    public function getEntityCurrentAction()
    {
        return $this->entityAction = $this->currentUser ?? null;
    }

    /**
     * @return \App\Entity\Team|null
     */
    public function getEntityTeam()
    {
        return $this->currentUser ? $this->currentUser->getTeam() : null;
    }

    /**
     * @return array
     */
    public function getTriggeredByDetails()
    {
        return $this->entityAction
            ? ['id' => $this->entityAction->getId(), 'value' => $this->entityAction->getEmail()]
            : ['value' => self::DEFAULT_TRIGGERED_BY];
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
