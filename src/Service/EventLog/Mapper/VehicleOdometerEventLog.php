<?php

namespace App\Service\EventLog\Mapper;

use App\Entity\Notification\Event;
use App\Entity\User;
use App\Entity\VehicleOdometer;
use App\Util\StringHelper;

class VehicleOdometerEventLog extends EventLog
{
    protected $context;

    /**
     * VehicleOdometerEventLog constructor.
     * @param VehicleOdometer $entity
     * @param User|null $currentUser
     * @param Event $event
     * @param array $context
     */
    public function __construct(VehicleOdometer $entity, ?User $currentUser, Event $event, $context = [])
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
        $team['team'] = ($this->entity->getVehicle()) ? $this->entity->getVehicle()->getTeam()->toArray() : null;
        $context['context'] = $this->context ?? null;
        $entityDetails = $this->entity->toArray(
            array_merge(VehicleOdometer::DEFAULT_DISPLAY_VALUES, ['device', 'vehicle', 'odometerFromDevice'])
        );

        return array_merge($entityDetails, $team, $context);
    }

    /**
     * @return string
     */
    public function getTeamData()
    {
        return $this->entity->getVehicle() ? $this->entity->getVehicle()->getTeam()->getClientName() : null;
    }

    /**
     * @return string
     */
    public function getEventSource()
    {
        return $this->entity->getVehicle()->getRegNo();
    }

    /**
     * @return User|mixed
     */
    public function getEntityCurrentAction()
    {
        //TODO change it, when change getting event log team throw createdBy
        return $this->entityAction = $this->entity->getCreatedBy();
    }

    /**
     * @return \App\Entity\Team|null
     */
    public function getEntityTeam()
    {
        return $this->entity->getVehicle() ? $this->entity->getVehicle()->getTeam() : null;
    }

    /**
     * @return \DateTime|null
     */
    public function getEventDate()
    {
        return $this->entity->getCreatedAt();
    }

    /**
     * @return string
     */
    public function getEventSourceType()
    {
        return StringHelper::getClassName(Event::ENTITY_TYPE_VEHICLE_ODOMETER);
    }

    /**
     * @return string
     */
    public function getTriggeredDetails()
    {
        return $this->entity && $this->entity->getCreatedBy()
            ? $this->entity->getCreatedBy()->getEmail()
            : self::DEFAULT_TRIGGERED_BY;
    }

    /**
     * @return array|string[]
     */
    public function getTriggeredByDetails()
    {
        return ($this->entity && $this->entity->getCreatedBy())
            ? [
                'id' => $this->entity->getCreatedBy()->getId(),
                'value' => $this->entity->getCreatedBy()->getEmail()
            ]
            : ['value' => self::DEFAULT_TRIGGERED_BY];
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
        return $this->entity?->getVehicle()?->getTeam()->getId();
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
