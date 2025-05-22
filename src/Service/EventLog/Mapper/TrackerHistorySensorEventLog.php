<?php

namespace App\Service\EventLog\Mapper;

use App\Entity\Notification\Event;
use App\Entity\Tracker\TrackerHistorySensor;
use App\Entity\User;
use App\Util\StringHelper;

class TrackerHistorySensorEventLog extends EventLog
{
    protected $context;

    /**
     * VehicleOdometerEventLog constructor.
     * @param TrackerHistorySensor $entity
     * @param User|null $currentUser
     * @param Event $event
     * @param array $context
     */
    public function __construct(TrackerHistorySensor $entity, ?User $currentUser, Event $event, $context = [])
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
        $team['team'] = ($this->entity->getDevice()) ? $this->entity->getDevice()->getTeam()->toArray() : null;
        $context['context'] = $this->context ?? null;
        $entityDetails = $this->entity->toArray(
            array_merge(TrackerHistorySensor::DEFAULT_DISPLAY_VALUES)
        );
        $deviceSensorStatus['deviceSensorStatus'] = $this->entity->getDeviceSensor()->getStatusText();

        return array_merge($entityDetails, $team, $context, $deviceSensorStatus);
    }

    /**
     * @return string
     */
    public function getTeamData()
    {
        return $this->entity->getDevice() ? $this->entity->getDevice()->getTeam()->getClientName() : null;
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
        return $this->entityAction = $this->entity->getVehicle() ? $this->entity->getVehicle()->getDriver() : null;
    }

    /**
     * @return \App\Entity\Team|null
     */
    public function getEntityTeam()
    {
        return $this->entity->getDevice() ? $this->entity->getDevice()->getTeam() : null;
    }

    /**
     * @return \DateTime|null
     */
    public function getEventDate()
    {
        return $this->entity->getOccurredAt();
    }

    /**
     * @return string
     */
    public function getEventSourceType()
    {
        return StringHelper::getClassName(Event::ENTITY_TYPE_TRACKER_HISTORY_SENSOR);
    }

    /**
     * @return string
     */
    public function getTriggeredDetails()
    {
        return $this->entity && $this->entity->getDeviceSensorBLEId()
            ? $this->entity->getDeviceSensorBLEId()
            : self::DEFAULT_TRIGGERED_BY;
    }

    /**
     * @return array|string[]
     */
    public function getTriggeredByDetails()
    {
        return $this->entity && $this->entity->getDeviceSensorBLEId()
            ? [
                'id' => $this->entity->getDeviceSensor()->getSensorId(),
                'value' => $this->entity->getDeviceSensorBLEId()
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
        return $this->entity?->getVehicle()?->getDriver()?->getId();
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
        return $this->entity?->getSensor()?->getId();
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
