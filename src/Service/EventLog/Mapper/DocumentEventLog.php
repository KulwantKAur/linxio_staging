<?php

namespace App\Service\EventLog\Mapper;

use App\Entity\Document;
use App\Entity\Notification\Event;
use App\Entity\User;
use App\Util\StringHelper;

class DocumentEventLog extends EventLog
{
    /**
     * DocumentEventLog constructor.
     * @param Document $entity
     * @param User|null $currentUser
     * @param Event $event
     */
    public function __construct(Document $entity, ?User $currentUser, Event $event)
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
        $entity = $this->entity->isDriverDocument() ? $this->entity->getDriver() : $this->entity->getVehicle();

        return $entity->getClient() ? $entity->getTeam()->getClientName() : $entity->getTeam()->getType();
    }

    /**
     * @return string
     */
    public function getEventSource()
    {
        return $this->entity->getTitle();
    }

    /**
     * @return int|null
     */
    public function getVehicleId()
    {
        return $this->entity->getVehicle() ? $this->entity->getVehicle()->getId() : null;
    }

    /**
     * @return int|null
     */
    public function getDriverId(): ?int
    {
        return $this->entity->isDriverDocument() ? $this->entity?->getDriver()?->getId() : null;
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
        if ($this->entity->isDriverDocument()) {
            return $this->entity?->getDriver()?->getTeam()->getId();
        } elseif ($this->entity->isVehicleDocument()) {
            return $this->entity?->getVehicle()?->getTeam()->getId();
        } elseif ($this->entity->isAssetDocument()) {
            return $this->entity?->getAsset()?->getTeam()->getId();
        }

        return null;
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
