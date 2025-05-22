<?php

namespace App\Service\EventLog\Mapper;

use App\Entity\DocumentRecord;
use App\Entity\Notification\Event;
use App\Entity\User;
use App\Util\StringHelper;

class DocumentRecordEventLog extends EventLog
{
    /**
     * DocumentEventLog constructor.
     * @param DocumentRecord $entity
     * @param User|null $currentUser
     * @param Event $event
     */
    public function __construct(DocumentRecord $entity, ?User $currentUser, Event $event)
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
        $team['team'] = ($this->entity->getDocument()) ? $this->entity->getDocument()->getTeam()->toArray() : null;
        $entityDetails = $this->entity->toArray(
            array_merge(DocumentRecord::DEFAULT_DISPLAY_VALUES, ['document'])
        );

        return array_merge($entityDetails, $team);
    }

    /**
     * @return string
     */
    public function getTeamData()
    {
        $entity = $this->entity->getDocument()->isDriverDocument()
            ? $this->entity->getDocument()->getDriver()
            : $this->entity->getDocument()->getVehicle();

        return $entity->getClient() ? $entity->getTeam()->getClientName() : $entity->getTeam()->getType();
    }

    /**
     * @return string
     */
    public function getEventSource()
    {
        $eventSource = $this->entity->getDocument()->isDriverDocument()
            ? $this->entity->getDocument()->getDriver()->getEmail()
            : $this->entity->getDocument()->getVehicle()->getRegNo();

        return $eventSource;
    }

    /**
     * @return string
     */
    public function getEventSourceType()
    {
        return StringHelper::getClassName(Event::ENTITY_TYPE_DOCUMENT);
    }

    /**
     * @return int|null
     */
    public function getVehicleId()
    {
        return ($this->entity->getDocument()->isVehicleDocument() && $this->entity->getDocument())
            ? $this->entity->getDocument()?->getVehicle()?->getId() : null;
    }

    /**
     * @return int|null
     */
    public function getDriverId(): ?int
    {
        return $this->entity?->getDocument()?->isDriverDocument()
            ? $this->entity->getDocument()?->getDriver()?->getId() : null;
    }

    /**
     * @return null
     */
    public function getEntityId()
    {
        return $this->entity?->getDocument()?->getId();
    }

    /**
     * @return int|null
     */
    public function getEntityTeamId(): ?int
    {
        if ($this->entity?->getDocument()->isDriverDocument()) {
            return $this->entity?->getDriver()?->getTeam()->getId();
        } elseif ($this->entity?->getDocument()->isVehicleDocument()) {
            return $this->entity?->getVehicle()?->getTeam()->getId();
        } elseif ($this->entity?->getDocument()->isAssetDocument()) {
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
