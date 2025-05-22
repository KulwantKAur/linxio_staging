<?php

namespace App\Service\Notification\Queue\Consumer;

use App\Entity\Notification\Message;
use App\Service\TrackerProvider\TrackerProviderService;
use App\Util\ExceptionHelper;
use Doctrine\ORM\EntityManager;
use OldSound\RabbitMqBundle\RabbitMq\ConsumerInterface;
use PhpAmqpLib\Message\AMQPMessage;
use Psr\Log\LoggerInterface;

class WebAppTransportConsumer implements ConsumerInterface
{
    private $em;
    private $logger;
    private TrackerProviderService $trackerProviderService;

    /**
     * WebAppTransportConsumer constructor.
     * @param EntityManager $em
     * @param LoggerInterface $logger
     * @param TrackerProviderService $trackerProviderService
     */
    public function __construct(
        EntityManager $em,
        LoggerInterface $logger,
        TrackerProviderService $trackerProviderService,
    ) {
        $this->em = $em;
        $this->logger = $logger;
        $this->trackerProviderService = $trackerProviderService;
    }

    /**
     * @param AMQPMessage $msg
     * @return mixed|void
     */
    public function execute(AMQPMessage $msg)
    {
        try {
            $body = json_decode($msg->getBody(), true);

            if (!empty($body['id'])) {
                /** @var Message $message */
                $message = $this->em->getRepository(Message::class)->find($body['id']);

                if ($message) {
                    $data = $message->toArray();
                    $data['id'] = (string)$data['id'];
                    $response = $this->trackerProviderService->webNotification($data);
                }
            }
        } catch (\Throwable $e) {
            $this->logger->error(ExceptionHelper::convertToJson($e));
        }
    }
}