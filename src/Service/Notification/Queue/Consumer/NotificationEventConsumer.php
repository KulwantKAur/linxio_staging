<?php

namespace App\Service\Notification\Queue\Consumer;

use App\Command\Traits\CommandLoggerTrait;
use App\Entity\Notification\Event;
use App\Entity\EventLog\EventLog;
use App\Service\Notification\NotificationCollectorService;
use App\Util\ExceptionHelper;
use Doctrine\ORM\EntityManager;
use OldSound\RabbitMqBundle\RabbitMq\ConsumerInterface;
use PhpAmqpLib\Message\AMQPMessage;
use Psr\Log\LoggerInterface;

class NotificationEventConsumer implements ConsumerInterface
{
    use CommandLoggerTrait;

    private $em;
    private $service;
    private $logger;

    public function __construct(EntityManager $em, NotificationCollectorService $service, LoggerInterface $logger)
    {
        $this->em = $em;
        $this->service = $service;
        $this->logger = $logger;
    }

    /**
     * @param AMQPMessage $msg
     * @return mixed|void
     */
    public function execute(AMQPMessage $msg)
    {
        try {
            $message = json_decode($msg->getBody(), true);
            $event = $this->em->getRepository(Event::class)->find($message['event_id']);
            $entity = $event ? $this->em->getRepository($event->getEntity())->find($message['entity_id']) : null;
            $eventLog = $this->em->getRepository(EventLog::class)->find($message['event_log_id']);
            $context = $message['context'] ?? [];

            if ($event && $entity && $eventLog) {
                $this->service->collect(
                    $event,
                    $entity,
                    $eventLog,
                    \DateTime::createFromFormat('Y-m-d H:i:s', $message['dt']),
                    $context
                );
            }
        } catch (\Throwable $e) {
            $this->logger->error(ExceptionHelper::convertToJson($e));
            $this->logException($e);
        }
    }
}
