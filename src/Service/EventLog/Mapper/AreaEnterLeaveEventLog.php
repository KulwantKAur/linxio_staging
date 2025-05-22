<?php

namespace App\Service\EventLog\Mapper;

use App\Entity\AreaHistory;
use App\Entity\Notification\Event;
use App\Entity\User;
use App\Util\StringHelper;

class AreaEnterLeaveEventLog extends EventLog
{
    protected $context;
    protected $dateTime;

    /**
     * AreaEnterLeaveEventLog constructor.
     * @param AreaHistory $entity
     * @param User|null $currentUser
     * @param Event $event
     * @param array $context
     * @param null $dateTime
     */
    public function __construct(
        AreaHistory $entity,
        ?User $currentUser,
        Event $event,
        array $context = [],
        $dateTime = null
    ) {
        $this->entity = $entity;
        $this->currentUser = $currentUser;
        $this->currentEvent = $event;
        $this->context = $context;
        $this->dateTime = $dateTime;
    }

    /**
     * @return array
     * @throws \Exception
     */
    public function getDetails()
    {
        $team['team'] = ($this->entity->getVehicle()) ? $this->entity->getVehicle()->getTeam()->toArray() : null;
        $entityDetails = $this->entity->toArray(array_merge(AreaHistory::DEFAULT_DISPLAY_VALUES, ['device']));
        $context['context'] = $this->context ?? null;

        return array_merge($entityDetails, $team, $context);
    }

    /**
     * @return string
     */
    public function getTeamData()
    {
        return $this->entity->getVehicle()->getTeam()->getClientName();
    }

    /**
     * @return string
     */
    public function getTriggeredDetails()
    {
        return $this->entity && $this->entity->getVehicle() && $this->entity->getVehicle()->getDriver()
            ? $this->entity->getVehicle()->getDriverName()
            : self::DEFAULT_TRIGGERED_UNKNOWN;
    }

    public function getTriggeredByDetails()
    {
        return ($this->entity && $this->entity->getVehicle() && $this->entity->getVehicle()->getDriver())
            ? [
                'id' => $this->entity->getVehicle()->getDriver()->getId(),
                'value' => $this->entity->getVehicle()->getDriverName()
            ]
            : ['value' => self::DEFAULT_TRIGGERED_UNKNOWN];
    }

    /**
     * @return array|mixed
     */
    public function getEventSource()
    {
        return $this->entity->getVehicle()->getRegNo();
    }

    /**
     * @return string
     */
    public function getEventSourceType()
    {
        return StringHelper::getClassName(Event::ENTITY_TYPE_VEHICLE);
    }

    /**
     * @return \DateTime|null
     * @throws \Exception
     */
    public function getEventDate()
    {
        switch ($this->currentEvent->getName()) {
            case Event::VEHICLE_GEOFENCE_ENTER:
                return $this->entity->getArrived();
                break;
            case Event::VEHICLE_GEOFENCE_LEAVE:
                return $this->entity->getDeparted();
            case Event::VEHICLE_OVERSPEEDING_INSIDE_GEOFENCE:
                return $this->dateTime ?? new \DateTime();
            default:
                return new \DateTime();
        }
    }

    /**
     * @return User|mixed
     */
    public function getEntityCurrentAction()
    {
        //TODO change it, when change getting event log team throw createdBy
        return $this->entityAction = $this->entity->getVehicle() ? $this->entity->getVehicle()->getCreatedBy() : null;
    }

    public function getEntityTeam()
    {
        return $this->entity->getVehicle() ? $this->entity->getVehicle()->getTeam() : null;
    }

    /**
     * @return int|null
     */
    public function getVehicleId()
    {
        return $this->entity?->getVehicle()?->getId();
    }

    /**
     * @return int|null
     */
    public function getDriverId(): ?int
    {
        return $this->entity?->getVehicle()?->getDriver()?->getId();
    }

    /**
     * @return null
     */
    public function getEntityId()
    {
        return $this->entity?->getId();
    }

    /**
     * @return int|null
     */
    public function getEntityTeamId(): ?int
    {
        return $this->entity?->getVehicle()?->getTeam()?->getId();
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
