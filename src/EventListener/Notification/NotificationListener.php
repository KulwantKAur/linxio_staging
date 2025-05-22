<?php

namespace App\EventListener\Notification;

use App\Entity\Notification\Event;
use App\Events\Notification\NotificationEvent;
use App\Service\EventLog\EventLogService;
use App\Service\Notification\Helper\EventQueueMessage;
use Doctrine\ORM\EntityManager;
use Doctrine\Common\Util\ClassUtils;
use PhpAmqpLib\Exception\AMQPConnectionClosedException;
use \Symfony\Component\EventDispatcher\EventSubscriberInterface;
use \OldSound\RabbitMqBundle\RabbitMq\Producer;

class NotificationListener implements EventSubscriberInterface
{
    private $em;
    private $producer;
    private $eventLogService;

    public function __construct(
        EntityManager $em,
        Producer $producer,
        EventLogService $eventLogService
    ) {
        $this->em = $em;
        $this->producer = $producer;
        $this->eventLogService = $eventLogService;
    }

    public static function getSubscribedEvents()
    {
        return [
            NotificationEvent::NAME => 'processingNotificationEvent',
        ];
    }

    /**
     * @param NotificationEvent $event
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function processingNotificationEvent(NotificationEvent $event): void
    {
        $eventEntities = $this->em->getRepository(Event::class)->getEventsByName($event->getEventName());

        if ($eventEntities) {
            foreach ($eventEntities as $eventEntity) {
                if ($eventEntity->getEntity() === ClassUtils::getClass($event->getEntity())) {
                    $eventLog = $this->eventLogService->create($eventEntity, $event);
                    $eventMessage = new EventQueueMessage($eventEntity, $event, $eventLog);
                    try {
                        $this->producer->publish($eventMessage);
                    } catch (AMQPConnectionClosedException $e) {
                        //don't log AMQPConnectionClosedException
                    }
                }
            }
        }
    }
}
