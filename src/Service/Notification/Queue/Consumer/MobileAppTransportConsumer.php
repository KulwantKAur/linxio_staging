<?php

namespace App\Service\Notification\Queue\Consumer;

use App\Entity\Notification\Message;
use App\Entity\Notification\NotificationMobileDevice;
use App\Service\Firebase\FCMService;
use App\Util\ExceptionHelper;
use Doctrine\ORM\EntityManager;
use OldSound\RabbitMqBundle\RabbitMq\ConsumerInterface;
use PhpAmqpLib\Message\AMQPMessage;
use Psr\Log\LoggerInterface;

class MobileAppTransportConsumer implements ConsumerInterface
{
    private $em;
    private $fcmService;
    private $logger;

    /**
     * MobileAppTransportConsumer constructor.
     * @param EntityManager $em
     * @param FCMService $fcmService
     * @param LoggerInterface $logger
     */
    public function __construct(
        EntityManager $em,
        FCMService $fcmService,
        LoggerInterface $logger
    ) {
        $this->em = $em;
        $this->fcmService = $fcmService;
        $this->logger = $logger;
    }

    /**
     * @param AMQPMessage $msg
     * @return mixed|void
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function execute(AMQPMessage $msg)
    {
        $body = json_decode($msg->getBody(), true);

        if (!empty($body['id'])) {
            /** @var Message $message */
            $message = $this->em->getRepository(Message::class)->find($body['id']);

            if ($message->getRecipient()) {
                /** @var NotificationMobileDevice[] $devices */
                $devices = $this->em->getRepository(NotificationMobileDevice::class)
                    ->findBy(['user' => $message->getRecipient()]);

                foreach ($devices as $device) {
                    try {
                        $this->fcmService->sendNotificationMsg($device, $message);
                    } catch (\Throwable $e) {
                        $this->logger->error(ExceptionHelper::convertToJson($e));
                    }
                }
            }
        }
    }
}
