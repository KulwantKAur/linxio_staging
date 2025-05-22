<?php

namespace App\Service\Notification\Queue\Consumer;

use App\Entity\Notification\Message;
use App\Service\Sms\SmsService;
use App\Util\ExceptionHelper;
use Doctrine\ORM\EntityManager;
use OldSound\RabbitMqBundle\RabbitMq\ConsumerInterface;
use PhpAmqpLib\Message\AMQPMessage;
use Psr\Log\LoggerInterface;

/**
 * Class SmsTransportConsumer
 * @package App\Service\Notification\Queue\Consumer
 *
 * @example
 * $smsMessage = new SmsQueueMessage($phone, $text);
 * $this->producer->publish($smsMessage);
 *
 * @todo add tests
 */
class SmsTransportConsumer implements ConsumerInterface
{
    private $smsService;
    private $logger;
    private $em;

    public function __construct(
        EntityManager $em,
        SmsService $smsService,
        LoggerInterface $logger
    ) {
        $this->smsService = $smsService;
        $this->logger = $logger;
        $this->em = $em;
    }

    /**
     * @param AMQPMessage $msg
     * @return mixed|void
     */
    public function execute(AMQPMessage $msg)
    {
        $body = json_decode($msg->getBody(), true);

        if (!empty($body['id'])) {
            /** @var Message $message */
            $message = $this->em->getRepository(Message::class)->find($body['id']);

            if ($message) {
                try {
                    $this->smsService->send(
                        $message->getRecipient(),
                        $message->getBodyMessage(),
                        true,
                        $message->getSender()
                    );
                } catch (\Throwable $e) {
                    $this->logger->error(ExceptionHelper::convertToJson($e));
                }
            }
        }
    }
}
