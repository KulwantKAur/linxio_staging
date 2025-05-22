<?php

namespace App\Service\Device\DeviceQueue\EngineOnTime;

use App\Entity\Device;
use App\Entity\Vehicle;
use App\Service\Device\DeviceQueue\EngineOnTime\Vendor\BaseVendor;
use App\Util\ExceptionHelper;
use Carbon\Carbon;
use Doctrine\ORM\EntityManager;
use OldSound\RabbitMqBundle\RabbitMq\ConsumerInterface;
use PhpAmqpLib\Message\AMQPMessage;
use Psr\Log\LoggerInterface;

/**
 * @deprecated Not using for now, using directly from TrackerService (EngineOnTimeService)
 */
class EngineOnTimeConsumer implements ConsumerInterface
{
    public const QUEUES_NUMBER = 2; // should be equal to number of queues in `config/rabbitmq.yaml`
    public const ROUTING_KEY_PREFIX = 'engine_on_time_device_'; // should be equal to `routing_keys` of queues

    private $em;
    private $logger;

    /**
     * @param \stdClass|null $lastTrackerHistoryData
     * @return array|null
     */
    private function formatLastTrackerHistoryData(?\stdClass $lastTrackerHistoryData): ?array
    {
        return $lastTrackerHistoryData
            ? [
                'ts' => Carbon::parse($lastTrackerHistoryData->tsISO8601),
                'engineOnTime' => $lastTrackerHistoryData->engineOnTime,
                'ignition' => $lastTrackerHistoryData->ignition,
            ] : null;
    }

    /**
     * @param array $thObjectsArray
     * @return array|null
     */
    private function formatTHObjectsArray(array $thObjectsArray): ?array
    {
        return array_map(function (\stdClass $thObject) {
            return [
                'ts' => Carbon::parse($thObject->tsISO8601),
                'engineOnTime' => $thObject->engineOnTime,
                'ignition' => $thObject->ignition,
                'id' => $thObject->id,
            ];
        }, $thObjectsArray);
    }

    /**
     * EngineOnTimeConsumer constructor.
     *
     * @param EntityManager $em
     * @param LoggerInterface $logger
     */
    public function __construct(EntityManager $em, LoggerInterface $logger)
    {
        $this->em = $em;
        $this->logger = $logger;
    }

    /**
     * @param AMQPMessage $msg
     * @return mixed|void
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \Doctrine\Persistence\Mapping\MappingException
     */
    public function execute(AMQPMessage $msg)
    {
        $message = json_decode($msg->getBody());

        if (!$message) {
            return;
        }

        $deviceId = $message->device_id;
        $device = $deviceId ? $this->em->getRepository(Device::class)->find($deviceId) : null;
        $lastTrackerHistoryData = property_exists($message, 'last_tracker_history')
            ? $this->formatLastTrackerHistoryData($message->last_tracker_history)
            : null;
        $deviceVendorName = $device ? $device->getVendorName() : null;
        $deviceModelName = $device ? $device->getModelName() : null;
        $trackerDataSet = property_exists($message, 'tracker_history_data')
            ? $this->formatTHObjectsArray($message->tracker_history_data)
            : [];
        /** @var Vehicle|null $vehicle */
        $vehicle = $device ? $device->getVehicle() : null;

        if (!$device || !$vehicle || !$deviceVendorName || !$deviceModelName) {
            return;
        }

        try {
            $vendorModel = BaseVendor::resolve($deviceVendorName, $this->em);
            $vehicle = $vendorModel->calc($deviceId, $trackerDataSet, $vehicle, $lastTrackerHistoryData);
        } catch (\Exception $exception) {
            $this->logger->error(ExceptionHelper::convertToJson($exception));
            return;
        }

        $this->em->flush();
        $this->em->clear();
    }
}
