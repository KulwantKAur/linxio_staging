<?php

namespace App\Service\Device\EngineHistoryQueue;

use App\Service\Redis\MemoryDbService;
use App\Service\Redis\Models\VehicleRedisModel;
use App\Util\ExceptionHelper;
use Carbon\Carbon;
use OldSound\RabbitMqBundle\RabbitMq\ConsumerInterface;
use PhpAmqpLib\Message\AMQPMessage;
use Psr\Log\LoggerInterface;

class EngineHistoryConsumer implements ConsumerInterface
{
    public const QUEUES_NUMBER = 3; // should be equal to number of queues in `config/rabbitmq.yaml`
    public const ROUTING_KEY_PREFIX = 'tracker_engine_history_'; // should be equal to `routing_keys` of queues

    public function __construct(
        private readonly LoggerInterface $logger,
        private readonly MemoryDbService $memoryDbService,
    ) {}

    /**
     * @param AMQPMessage $msg
     * @return void
     * @throws \Doctrine\DBAL\ConnectionException
     */
    public function execute(AMQPMessage $msg)
    {
        try {
            $message = json_decode($msg->getBody());

            if (!$message) {
                return;
            }

            $vehicleId = $message->vehicle_id;
            $driverId = $message->driver_id;
            $deviceId = $message->device_id;
            $ignition = $message->ignition;
            $timestamp = $message->timestamp;
            $speed = $message->speed;
            $isFixWithSpeed = $message->is_fix_with_speed;
            $cacheKey = VehicleRedisModel::getEngineHistoryKey($vehicleId);
            $cacheData = $this->memoryDbService->getFromJson($cacheKey);

            if ($cacheData) {
                $engineOffStartedTs = $cacheData['engineOffStartedTs'];
                $cacheData['driverId'] = $cacheData['driverId'] == $driverId ? $cacheData['driverId'] : $driverId;

                if (!is_null($engineOffStartedTs)) {
                    if ($ignition === 0
                        || ($isFixWithSpeed && $speed === 0)
                        || ($ignition === null && !$isFixWithSpeed)
                    ) {
                        $cacheData['engineOffFinishedTs'] = $timestamp;
                        $cacheData['duration'] = $timestamp - $engineOffStartedTs;
                    } else {
                        $cacheData['engineOffStartedTs'] = null;
                        $cacheData['engineOffFinishedTs'] = null;
                        $cacheData['duration'] = 0;
//                        $cacheData['isTriggerred'] = false;
                    }
                } else {
                    if ($ignition === 0 || ($isFixWithSpeed && $speed === 0)) {
                        $cacheData['engineOffStartedTs'] = $timestamp;
                        $cacheData['engineOffFinishedTs'] = null;
                        $cacheData['duration'] = 0;
                    }
                }

                $this->memoryDbService->setToJson($cacheKey, $cacheData);
            } else {
                $this->memoryDbService->setToJson($cacheKey, [
                    'driverId' => $driverId,
                    'deviceId' => $deviceId,
                    'engineOffStartedTs' => $ignition === 0 || ($isFixWithSpeed && $speed === 0) ? $timestamp : null,
                    'engineOffFinishedTs' => null,
                    'duration' => 0
                ]);
            }
        } catch (\Throwable $e) {
            $this->logger->error(ExceptionHelper::convertToJson($e));
        }
    }
}
