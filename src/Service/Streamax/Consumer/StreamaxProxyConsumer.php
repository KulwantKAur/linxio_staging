<?php

namespace App\Service\Streamax\Consumer;

use App\Service\Streamax\StreamaxService;
use App\Util\ExceptionHelper;
use OldSound\RabbitMqBundle\RabbitMq\ConsumerInterface;
use PhpAmqpLib\Message\AMQPMessage;
use Psr\Log\LoggerInterface;

class StreamaxProxyConsumer implements ConsumerInterface
{
    public function __construct(
        private StreamaxService $streamaxService,
        private LoggerInterface $logger
    ) {
    }

    public function execute(AMQPMessage $msg)
    {
        $message = json_decode($msg->getBody(), true);

        try {
            if (!$message) {
                return;
            }

            $data = $message['data'];
            $this->streamaxService->setRequestLogId($message['streamaxLogId'] ?? null);
//            $this->logger->notice(json_encode($data), [
//                'streamax_log_id' => $this->streamaxService->getRequestLogId(),
//                'streamax_proxy_time_before_parseFromTcpProxy' => (new \DateTime())->format('Y-m-d H:i:s')
//            ]);
            $this->streamaxService->parseFromTcpProxy($data);
//            $this->logger->notice('streamax_proxy_after_parseFromTcp', [
//                'streamax_log_id' => $this->streamaxService->getRequestLogId(),
//                'streamax_proxy_time_after_parseFromTcpProxy' => (new \DateTime())->format('Y-m-d H:i:s')
//            ]);
        } catch (\Exception $exception) {
            $this->logger->error(ExceptionHelper::convertToJson($exception), ['data' => $data]);
            return false;
        }
    }
}
