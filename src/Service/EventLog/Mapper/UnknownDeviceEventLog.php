<?php

namespace App\Service\EventLog\Mapper;

use App\Entity\Notification\Event;
use App\Entity\Team;
use App\Entity\Tracker\TrackerAuthUnknown;
use App\Util\StringHelper;

class UnknownDeviceEventLog extends EventLog
{

    /**
     * AreaEnterLeaveEventLog constructor.
     * @param TrackerAuthUnknown $entity
     * @param Event $event
     */
    public function __construct(TrackerAuthUnknown $entity, Event $event)
    {
        $this->entity = $entity;
        $this->currentEvent = $event;
    }

    /**
     * @return string
     */
    public function getTeamData()
    {
        return Team::TEAM_ADMIN;
    }

    /**
     * @return array|mixed
     */
    public function getEventSource()
    {
        return $this->entity->getImei() ?? null;
    }

    /**
     * @return string
     */
    public function getEventSourceType()
    {
        return StringHelper::getClassName(Event::ENTITY_TYPE_DEVICE);
    }

    /**
     * @return null
     */
    public function getEntityId()
    {
        return $this->entity->getId();
    }

}
