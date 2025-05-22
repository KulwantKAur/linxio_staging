<?php

namespace App\Service\EventLog\Mapper;

use App\Entity\Device;
use App\Entity\Idling;
use App\Entity\Notification\Event;
use App\Entity\User;
use App\Util\StringHelper;
use Doctrine\ORM\Mapping\Id;

class IdlingEventLog extends EventLog
{

    /**
     * IdlingEventLog constructor.
     * @param Idling $entity
     * @param User|null $currentUser
     * @param Event $event
     */
    public function __construct(Idling $entity, ?User $currentUser, Event $event)
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
        $team['team'] = ($this->entity->getVehicle()) ? $this->entity->getVehicle()->getTeam()->toArray() : null;
        $entityDetails = $this->entity->toArray(array_merge(Idling::DEFAULT_DISPLAY_VALUES, ['device', 'vehicle']));

        return array_merge($entityDetails, $team);
    }

    /**
     * @return string
     */
    public function getTeamData()
    {
        $teamData = ($this->entity->getDevice())
            ? $this->entity->getDevice()->getTeam()->getClientName()
            : null;

        return $teamData;
    }

    /**
     * @return string
     */
    public function getEventSource()
    {
        return $this->entity->getVehicle() ? $this->entity->getVehicle()->getRegNo() : '--';
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
    public function getEventSourceType()
    {
        return StringHelper::getClassName(Event::ENTITY_TYPE_VEHICLE);
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
        return $this->entity?->getDevice()?->getTeamId();
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
