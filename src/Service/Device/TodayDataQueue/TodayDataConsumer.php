<?php

namespace App\Service\Device\TodayDataQueue;

use App\Service\Redis\MemoryDbService;
use App\Service\Redis\Models\VehicleRedisModel;
use App\Util\ExceptionHelper;
use Carbon\Carbon;
use OldSound\RabbitMqBundle\RabbitMq\ConsumerInterface;
use PhpAmqpLib\Message\AMQPMessage;
use Psr\Log\LoggerInterface;

class TodayDataConsumer implements ConsumerInterface
{
    private $logger;
    private MemoryDbService $memoryDbService;

    public const QUEUES_NUMBER = 3; // should be equal to number of queues in `config/rabbitmq.yaml`
    public const ROUTING_KEY_PREFIX = 'today_data_device_'; // should be equal to `routing_keys` of queues

    private function getIdleDefaultTs(\stdClass $message, ?array $cache): ?int
    {
        $isIdle = $message->ignition && !$message->movement;
        $idleTsFromCache = $cache['idleTs'] ?? null;

        return $isIdle ? $message->timestamp : $idleTsFromCache;
    }

    private function getIdleData(\stdClass $message, array $cache): array
    {
        $isIdle = $message->ignition && !$message->movement;
        $idleTsFromCache = $cache['idleTs'] ?? null;
        $idleTsForCache = $this->getIdleDefaultTs($message, $cache);
        $duration = isset($cache['idleDuration'])
            ? (($idleTsForCache && $idleTsFromCache)
                ? $cache['idleDuration'] + $idleTsForCache - $idleTsFromCache
                : $cache['idleDuration'])
            : 0;

        return [
            'idleTs' => $isIdle ? $idleTsForCache : null,
            'idleDuration' => $duration,
        ];
    }

    public function __construct(
        LoggerInterface $logger,
        MemoryDbService $memoryDbService
    ) {
        $this->logger = $logger;
        $this->memoryDbService = $memoryDbService;
    }

    public function execute(AMQPMessage $msg)
    {
        try {
            $message = json_decode($msg->getBody());
            if (!$message) {
                return;
            }

            $vehicleId = $message->vehicle_id;
            if (!$vehicleId || !$message->timezone) {
                return;
            }

            $timezone = $message->timezone;
            $now = (new Carbon())->setTimezone($timezone);
            $key = VehicleRedisModel::getTodayDataKey($vehicleId);
            $cache = $this->memoryDbService->getFromJson($key);
            $isDriving = $message->movement;
            $ts = Carbon::createFromTimestamp($message->timestamp, $timezone);
            $tsFromCache = $cache['ts'] ?? null;
            $tsForCache = $isDriving ? $message->timestamp : $tsFromCache;
            $firstTsWithTimezone = isset($cache['firstTs']) && $cache['firstTs']
                ? Carbon::createFromTimestamp($cache['firstTs'], $timezone) : null;

            if ($cache && $firstTsWithTimezone && $ts && $now->day === $ts->day
                && $firstTsWithTimezone->day === $now->day
            ) {
                if ($message->speed) {
                    $cache['speed'][] = $message->speed;
                }
                if ($tsForCache && $tsFromCache) {
                    $duration = $cache['duration'] + $tsForCache - $tsFromCache;
                } else {
                    $duration = $cache['duration'];
                }

//                if ($message->odometer && $cache['odometer']) {
//                    $distance = $cache['distance'] + ($message->odometer - $cache['odometer']);
//                } else {
//                    $distance = 0;
//                }

                $newData = [
                    'duration' => $duration,
//                    'distance' => $distance,
                    'distance' => ($cache['firstOdometer'] && $message->odometer)
                        ? $message->odometer - $cache['firstOdometer']
                        : 0,
                    'avgSpeed' => count($cache['speed']) ? array_sum($cache['speed']) / count($cache['speed']) : 0,
                    'odometer' => $message->odometer ?? $cache['odometer'],
                    'speed' => $cache['speed'],
                    'ts' => $isDriving ? $tsForCache : null,
                    'firstTs' => $cache['firstTs'] ?? $message->timestamp,
                    'firstOdometer' => $cache['firstOdometer'] ?? $message->odometer,
                    ...$this->getIdleData($message, $cache)
                ];
                $this->memoryDbService->setToJson($key, $newData);
            } else {
                $this->memoryDbService->setToJson($key, [
                    'duration' => 0,
                    'distance' => 0,
                    'speed' => $message->speed ? [$message->speed] : [],
                    'avgSpeed' => $message->speed,
                    'odometer' => $message->odometer,
                    'ts' => $tsForCache,
                    'firstTs' => $message->timestamp,
                    'firstOdometer' => $message->odometer,
                    'idleDuration' => 0,
                    'idleTs' => $this->getIdleDefaultTs($message, $cache),
                ]);
            }
        } catch (\Throwable $e) {
            $this->logger->error(ExceptionHelper::convertToJson($e), ['vehicleId' => $vehicleId]);
        }
    }
}
