<?php

namespace App\Service\Notification\Helper;

use App\Entity\Notification\Event;
use App\Entity\EventLog\EventLog;
use App\Events\Notification\NotificationEvent;

class EventQueueMessage
{
    private $event;
    private $notificationEvent;
    private $eventLog;

    public function __construct(
        Event $event,
        NotificationEvent $notificationEvent,
        EventLog $eventLog
    ) {
        $this->event = $event;
        $this->notificationEvent = $notificationEvent;
        $this->eventLog = $eventLog;
    }

    /**
     * @return false|string
     * @throws \Exception
     */
    public function __toString()
    {
        $date = (new \DateTime())->format('Y-m-d H:i:s');
        if ($this->notificationEvent->getDt()) {
            $date = $this->notificationEvent->getDt()->format('Y-m-d H:i:s');
        } elseif ($this->eventLog->getEventDate()) {
            $date = $this->eventLog->getEventDate()->format('Y-m-d H:i:s');
        }

        return json_encode(
            [
                'event_id' => $this->event->getId(),
                'entity_id' => $this->notificationEvent->getEntity()->getId(),
                'event_log_id' => $this->eventLog->getId(),
                'dt' => $date,
                'context' => $this->notificationEvent->getContext()
            ]
        );
    }
}