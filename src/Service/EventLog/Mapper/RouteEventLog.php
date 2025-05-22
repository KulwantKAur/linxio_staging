<?php

namespace App\Service\EventLog\Mapper;

use App\Entity\Notification\Event;
use App\Entity\Route;
use App\Entity\User;

class RouteEventLog extends EventLog
{

    protected $context;

    /**
     * RouteEventLog constructor.
     * @param Route $entity
     * @param User|null $currentUser
     * @param Event $event
     * @param array $context
     */
    public function __construct(Route $entity, ?User $currentUser, Event $event, $context = [])
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
            array_merge(
                Route::DEFAULT_DISPLAY_VALUES,
                ['device', 'vehicle', 'duration']
            )
        );


        return array_merge($entityDetails, $team, $context);
    }

    /**
     * @return string
     */
    public function getTeamData()
    {
        $teamData = ($this->entity->getVehicle())
            ? $this->entity->getVehicle()->getTeam()->getClientName()
            : null;

        return $teamData;
    }

    /**
     * @return string
     */
    public function getEventSource()
    {
        return $this->entity->getVehicle() ? $this->entity->getVehicle()->getRegNo() : null;
    }

    /**
     * @return User|mixed
     */
    public function getEntityCurrentAction()
    {
        //TODO change it, when change getting event log team throw createdBy
        return $this->entityAction = $this->entity->getVehicle() ? $this->entity->getVehicle()->getCreatedBy() : null;
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
        return $this->entity->getStartedAt();
    }

    /**
     * @return string
     */
    public function getTriggeredDetails()
    {
        return $this->entity && $this->entity->getVehicle() && $this->entity->getVehicle()->getDriver()
            ? $this->entity->getVehicle()->getDriverName()
            : self::DEFAULT_TRIGGERED_BY;
    }

    public function getTriggeredByDetails()
    {
        return ($this->entity && $this->entity->getVehicle() && $this->entity->getVehicle()->getDriver())
            ? [
                'id' => $this->entity->getVehicle()->getDriver()->getId(),
                'value' => $this->entity->getVehicle()->getDriverName()
            ]
            : ['value' => self::DEFAULT_TRIGGERED_BY];
    }

    /**
     * @return int|null
     */
    public function getVehicleId()
    {
        if ($this->entity->getVehicle()) {
            return $this->entity?->getVehicle()?->getId();
        } else {
            return $this->entity?->getDevice()?->getVehicle()?->getId();
        }
    }


    /**
     * @return int|null
     */
    public function getDriverId(): ?int
    {
        return $this->entity?->getDriver()?->getId();
    }

    /**
     * @return int|null
     */
    public function getDeviceId(): ?int
    {
        return $this->entity?->getDevice()?->getId();
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
        return $this->entity?->getDevice()?->getTeam()?->getId();
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
