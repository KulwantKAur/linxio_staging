<?php

namespace App\Service\Device\DeviceIOQueue;

use App\Entity\Device;
use App\Entity\Tracker\TrackerHistory;
use App\Entity\Vehicle;
use App\Service\Device\DeviceIOQueue\Vendor\BaseVendor;
use App\Service\Notification\EventDispatcher as NotificationEventDispatcher;
use App\Util\ExceptionHelper;
use Doctrine\ORM\EntityManager;
use OldSound\RabbitMqBundle\RabbitMq\ConsumerInterface;
use PhpAmqpLib\Message\AMQPMessage;
use Psr\Log\LoggerInterface;

class IOConsumer implements ConsumerInterface
{
    public const QUEUES_NUMBER = 6; // should be equal to number of queues in `config/rabbitmq.yaml`
    public const ROUTING_KEY_PREFIX = 'io_device_'; // should be equal to `routing_keys` of queues

    private $em;
    private $logger;
    private $notificationDispatcher;

    /**
     * @param EntityManager $em
     * @param LoggerInterface $logger
     * @param NotificationEventDispatcher $notificationDispatcher
     */
    public function __construct(
        EntityManager $em,
        LoggerInterface $logger,
        NotificationEventDispatcher $notificationDispatcher
    ) {
        $this->em = $em;
        $this->logger = $logger;
        $this->notificationDispatcher = $notificationDispatcher;
    }

    /**
     * @param AMQPMessage $msg
     * @return false|void
     */
    public function execute(AMQPMessage $msg)
    {
        $message = json_decode($msg->getBody());

        try {
            if (!$message) {
                return;
            }

            $deviceId = $message->device_id;
            $device = $deviceId ? $this->em->getRepository(Device::class)->getDeviceWithVehicle($deviceId) : null;
            $deviceVendorName = $device?->getVendorName();
            $deviceModelName = $device?->getModelName();
            $trackerHistoriesIds = $message->tracker_history_ids;
            $trackerHistories = $this->em->getRepository(TrackerHistory::class)
                ->getTrackerHistoriesByIds($trackerHistoriesIds);
            /** @var Vehicle|null $vehicle */
            $vehicle = $device?->getVehicle();

            if (!$device || !$trackerHistoriesIds || !$trackerHistories || !$vehicle || !$deviceVendorName ||
                !$deviceModelName
            ) {
                return;
            }

            $vendorIOModel = BaseVendor::resolve($deviceVendorName, $this->em, $this->notificationDispatcher);
            $vehicle = $vendorIOModel->calc($deviceId, $trackerHistories, $vehicle);

            $this->em->flush();
            $this->em->clear();
        } catch (\Exception $exception) {
            $this->logger->error(ExceptionHelper::convertToJson($exception));
            return false;
        }
    }
}
