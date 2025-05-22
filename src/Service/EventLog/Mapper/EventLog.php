<?php

namespace App\Service\EventLog\Mapper;

use App\Util\StringHelper;
use Doctrine\Common\Util\ClassUtils;

abstract class EventLog
{
    public const DEFAULT_TRIGGERED_BY = 'system';
    public const DEFAULT_TRIGGERED_UNKNOWN = '-';

    protected $currentUser;
    protected $entity;
    protected $entityAction;
    protected $currentEvent;

    /**
     * @return mixed
     */
    abstract public function getTeamData();

    /**
     * @return mixed
     */
    abstract public function getEventSource();

    /**
     * @return array
     * @throws \Exception
     */
    public function getDetails()
    {
        return $this->entity->toArray();
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
     * @return mixed
     */
    public function getEntityCurrentAction()
    {
        return $this->entityAction = $this->currentUser;
    }

    /**
     * @return |null
     */
    public function getEntityTeam()
    {
        return $this->currentUser ? $this->currentUser->getTeam() : null;
    }

    /**
     * @return string
     */
    public function getTriggeredDetails()
    {
        return ($this->entityAction) ? $this->entityAction->getEmail() : self::DEFAULT_TRIGGERED_BY;
    }

    /**
     * @return array
     */
    public function getTriggeredByDetails()
    {
        return $this->entityAction
            ? ['id' => $this->entityAction->getId(), 'value' => $this->entityAction->getEmail()]
            : ['value' => self::DEFAULT_TRIGGERED_BY];
    }

    /**
     * @return string
     */
    public function getEventSourceType()
    {
        return StringHelper::getClassName($this->entity);
    }

    /**
     * @return \DateTime
     * @throws \Exception
     */
    public function getEventDate()
    {
        return new \DateTime();
    }

    /**
     * @return null
     */
    public function getVehicleId()
    {
        return null;
    }

    /**
     * @return int|null
     */
    public function getDeviceId(): ?int
    {
        return null;
    }

    /**
     * @return int|null
     */
    public function getDriverId(): ?int
    {
        return null;
    }

    /**
     * @return null
     */
    public function getShortDetails()
    {
        return null;
    }

    /**
     * @return null
     */
    public function getEntityId()
    {
        return null;
    }

    /**
     * @return int|null
     */
    public function getEntityTeamId(): ?int
    {
        return null;
    }

    /**
     * @return int|null
     */
    public function getTeamBy(): ?int
    {
        return null;
    }

    /**
     * @return int|null
     */
    public function getUserBy(): ?int
    {
        return null;
    }
}
