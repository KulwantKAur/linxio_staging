<?php

namespace App\Service\Streamax\Consumer;

use App\Service\Streamax\StreamaxService;
use App\Util\ExceptionHelper;
use OldSound\RabbitMqBundle\RabbitMq\ConsumerInterface;
use PhpAmqpLib\Message\AMQPMessage;
use Psr\Log\LoggerInterface;

class StreamaxPostponedConsumer implements ConsumerInterface
{
    private function processAlarmVideos(array $data): void
    {
        $deviceId = $data['deviceId'];
        $payload = $data['payload'];
        $trackerHistoryIDs = $data['trackerHistoryIDs'];
        $this->streamaxService->processAlarmVideos($deviceId, $payload, $trackerHistoryIDs);
    }

    private function handleDownloadState(array $data): void
    {
        $this->streamaxService->handleDownloadState($data);
    }

    public function __construct(
        private StreamaxService $streamaxService,
        private LoggerInterface $logger
    ) {
    }

    /**
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
            $type = $message['type'];
//            $this->logger->notice(json_encode($data) . ", type: $type");

            match ($type) {
                'processAlarmVideos' => $this->processAlarmVideos($data),
                'handleDownloadState' => $this->handleDownloadState($data),
            };
        } catch (\Exception $exception) {
            $this->logger->error(ExceptionHelper::convertToJson($exception), ['data' => $data, 'type' => $type]);
            return false;
        }
    }
}
