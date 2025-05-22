<?php

namespace App\Service\EventLog\Mapper;

use App\Entity\Device;
use App\Entity\Notification\Event;
use App\Entity\Speeding;
use App\Entity\User;
use App\Util\StringHelper;

class SpeedingEventLog extends EventLog
{

    /**
     * TrackerHistoryEventLog constructor.
     * @param Speeding $entity
     * @param User|null $currentUser
     * @param Event $event
     */
    public function __construct(Speeding $entity, ?User $currentUser, Event $event)
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
        $entityDetails = $this->entity->toArray(
            array_merge(Speeding::DEFAULT_DISPLAY_VALUES, ['vehicle', 'device'])
        );

        return array_merge($entityDetails, $team);
    }

    /**
     * @return string
     */
    public function getTeamData()
    {
        $teamData = ($this->entity->getDevice())
            ? $this->entity->getDevice()->getTeam()->getType()
            : null;

        return $teamData;
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
        return $this->entity->getVehicle() ? $this->entity->getVehicle()->getId() : null;
    }
}
