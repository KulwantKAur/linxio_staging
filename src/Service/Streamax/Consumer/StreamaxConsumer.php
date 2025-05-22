<?php

namespace App\Service\Streamax\Consumer;

use App\Service\Streamax\StreamaxService;
use App\Util\ExceptionHelper;
use OldSound\RabbitMqBundle\RabbitMq\ConsumerInterface;
use PhpAmqpLib\Message\AMQPMessage;
use Psr\Log\LoggerInterface;

class StreamaxConsumer implements ConsumerInterface
{
    public const QUEUES_NUMBER = 1; // should be equal to number of queues in `config/rabbitmq.yaml`
    public const ROUTING_KEY_PREFIX = 'tracker_streamax_'; // should be equal to `routing_keys` of queues

    /**
     * @param StreamaxService $streamaxService
     * @param LoggerInterface $logger
     */
    public function __construct(
        private StreamaxService $streamaxService,
        private LoggerInterface $logger
    ) {
    }

    /**
     * @param AMQPMessage $msg
     * @return false|void
     */
    public function execute(AMQPMessage $msg)
    {
        $message = json_decode($msg->getBody(), true);

        try {
            if (!$message) {
                return;
            }

            $data = $message['data'];
            $this->streamaxService->setRequestLogId($message['streamaxLogId'] ?? null);
            $this->logger->notice(json_encode($data), [
                'streamax_log_id' => $this->streamaxService->getRequestLogId(),
                'streamax_time_before_parseFromTcp' => (new \DateTime())->format('Y-m-d H:i:s')
            ]);
            $this->streamaxService->parseFromTcp($data);
            $this->logger->notice('streamax_after_parseFromTcp', [
                'streamax_log_id' => $this->streamaxService->getRequestLogId(),
                'streamax_time_after_parseFromTcp' => (new \DateTime())->format('Y-m-d H:i:s')
            ]);
        } catch (\Exception $exception) {
            $this->logger->error(ExceptionHelper::convertToJson($exception), ['data' => $data]);
            return false;
        }
    }
}
