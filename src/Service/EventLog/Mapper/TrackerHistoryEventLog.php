<?php

namespace App\Service\EventLog\Mapper;

use App\Entity\EventLog\EventLog;
use App\Entity\Notification\Event;
use App\Entity\Tracker\TrackerHistory;
use App\Entity\User;
use App\Service\MapService\MapServiceInterface;
use App\Util\StringHelper;

class TrackerHistoryEventLog extends EventLog
{
    protected array $context;
    protected TrackerHistory $entity;
    protected Event $currentEvent;
    protected ?User $currentUser;
    protected $entityAction;

    public function __construct(
        TrackerHistory $entity,
        ?User $currentUser,
        Event $event,
        private readonly MapServiceInterface $mapService,
        $context = []
    ) {
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
        $entityDetails = $this->entity->toArray();
        $additionalData['device'] = ($this->entity->getDevice())
            ? $this->entity->getDevice()->toArray()
            : null;
        $context['context'] = $this->context ?? [];
        $context['context'][EventLog::ADDRESS] = $context['context'][EventLog::ADDRESS]
            ?? ($this->entity->getLat() && $this->entity->getLng()
                ? $this->mapService->getLocationByCoordinates($this->entity->getLat(), $this->entity->getLng())
                : null
            );

        return array_merge($entityDetails, $additionalData, $context);
    }

    /**
     * @return string
     */
    public function getTeamData()
    {
        return $this->entity->getDevice()?->getTeam()->getClientName();
    }

    /**
     * @return string
     */
    public function getEventSource()
    {
        return $this->entity->getVehicle() ? $this->entity->getVehicle()->getRegNo() : '--';
    }

    /**
     * @return array|mixed
     */
    public function getEventDetails()
    {
        return [
            'eventSource' => $this->getEventSource(),
            'eventTeam' => $this->getTeamData(),
        ];
    }

    /**
     * @return User|mixed
     */
    public function getEntityCurrentAction()
    {
        //TODO change it, when change getting event log team throw createdBy
        return $this->entityAction = $this->entity->getVehicle()
            ? $this->entity->getVehicle()->getCreatedBy()
            : $this->entity->getDevice()->getCreatedBy();
    }

    /**
     * @return \App\Entity\Team|null
     */
    public function getEntityTeam()
    {
        if ($this->entity->getVehicle()) {
            return $this->entity->getVehicle()->getTeam();
        } else {
            return $this->entity->getDevice()?->getTeam();
        }
    }

    /**
     * @return string
     */
    public function getEventSourceType()
    {
        return StringHelper::getClassName(Event::ENTITY_TYPE_DEVICE);
    }

    /**
     * @return \DateTime|\DateTimeImmutable
     */
    public function getEventDate()
    {
        return $this->entity->getTs();
    }

    /**
     * @return string
     */
    public function getTriggeredDetails()
    {
        return $this->entity?->getVehicle()?->getDriverName() ?? \App\Service\EventLog\Mapper\EventLog::DEFAULT_TRIGGERED_UNKNOWN;
    }

    /**
     * @return array
     */
    public function getTriggeredByDetails()
    {
        return $this->entity?->getVehicle()?->getDriver()
            ? [
                'id' => $this->entity->getVehicle()->getDriver()->getId(),
                'value' => $this->entity->getVehicle()->getDriverName()
            ]
            : ['value' => \App\Service\EventLog\Mapper\EventLog::DEFAULT_TRIGGERED_BY];
    }

    /**
     * @return int|null
     */
    public function getVehicleId(): ?int
    {
        return $this->entity?->getVehicle()?->getId();
    }

    /**
     * @return int|null
     */
    public function getDriverId(): ?int
    {
        return $this->entity->getVehicle()?->getDriver()?->getId();
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
    public function getEntityId(): ?int
    {
        return $this->entity?->getId();
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
