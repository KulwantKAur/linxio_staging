<?php

namespace App\Service\EventLog\Mapper;

use App\Entity\Notification\Event;
use App\Entity\User;
use App\Entity\Vehicle;

class VehicleEventLog extends EventLog
{
    protected $context;
    protected ?\DateTime $dateTime;

    /**
     * VehicleEventLog constructor.
     * @param Vehicle $entity
     * @param User|null $currentUser
     * @param Event $event
     * @param array $context
     * @param \DateTime|null $dt
     */
    public function __construct(Vehicle $entity, ?User $currentUser, Event $event, $context = [], ?\DateTime $dt = null)
    {
        $this->entity = $entity;
        $this->currentUser = $currentUser;
        $this->currentEvent = $event;
        $this->context = $context;
        $this->dateTime = $dt;
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
        $teamData = ($this->entity->getClient())
            ? $this->entity->getClientName()
            : $this->entity->getTeam()->getType();

        return $teamData;
    }

    /**
     * @return string
     */
    public function getEventSource()
    {
        switch ($this->currentEvent->getName()) {
            case Event::VEHICLE_CHANGED_MODEL:
                return $this->entity->getModel();
            default:
                return $this->entity->getRegNo();
        }
    }

    /**
     * @return mixed
     */
    public function getEntityCurrentAction()
    {
        switch ($this->currentEvent->getName()) {
            case Event::VEHICLE_REASSIGNED:
                if ($this->currentUser) {
                    return $this->entityAction = $this->currentUser;
                }
                return $this->entityAction = $this->entity->getDriver() ? $this->entity->getDriver() : null;
            default:
                return $this->entityAction = $this->currentUser ?? null;
        }
    }

    /**
     * @return \App\Entity\Team|null
     */
    public function getEntityTeam()
    {
        switch ($this->currentEvent->getName()) {
            case Event::VEHICLE_REASSIGNED:
                return $this->currentUser ? $this->currentUser->getTeam() : ($this->entity->getTeam() ? $this->entity->getTeam() : null);
            default:
                return $this->currentUser ? $this->currentUser->getTeam() : null;
        }
    }

    /**
     * @return string
     */
    public function getTriggeredDetails()
    {
        switch ($this->currentEvent->getName()) {
            case Event::VEHICLE_REASSIGNED:
                return ($this->entityAction) ? $this->entityAction->getEmail() : ($this->entity && $this->entity->getDriver()
                    ? $this->entity->getDriverName()
                    : self::DEFAULT_TRIGGERED_BY);
            default:
                return ($this->entityAction) ? $this->entityAction->getEmail() : self::DEFAULT_TRIGGERED_BY;
        }
    }

    /**
     * @return array
     */
    public function getTriggeredByDetails()
    {
        switch ($this->currentEvent->getName()) {
            case Event::VEHICLE_REASSIGNED:
                return $this->entityAction ? [
                    'id' => $this->entityAction->getId(),
                    'value' => $this->entityAction->getEmail()
                ] : ($this->entity && $this->entity->getDriver()
                    ? [
                        'id' => $this->entity->getDriver() ? $this->entity->getDriver()->getId() : null,
                        'value' => $this->entity->getDriverName()
                    ]
                    : ['value' => self::DEFAULT_TRIGGERED_BY]);
            default:
                return $this->entityAction
                    ? ['id' => $this->entityAction->getId(), 'value' => $this->entityAction->getEmail()]
                    : ['value' => self::DEFAULT_TRIGGERED_BY];
        }
    }

    /**
     * @return int|null
     */
    public function getVehicleId()
    {
        return $this->entity->getId() ? $this->entity->getId() : null;
    }

    /**
     * @return int|null
     */
    public function getDriverId(): ?int
    {
        return $this->entity?->getDriver()?->getId();
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

    /**
     * @return \DateTime|null
     */
    public function getDateTime(): ?\DateTime
    {
        return $this->dateTime;
    }

    /**
     * @return \DateTime|null
     * @throws \Exception
     */
    public function getEventDate()
    {
        return $this->getDateTime() ?: parent::getEventDate();
    }

    public function getDeviceId(): ?int
    {
        return $this->entity?->getDevice()?->getId();
    }
}
