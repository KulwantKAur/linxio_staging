<?php

namespace App\Service\Device\DeviceBatteryQueue;

use App\Entity\Device;
use App\Entity\Notification\Event;
use App\Entity\Notification\Notification;
use App\Entity\Tracker\TrackerHistory;
use App\EntityManager\SlaveEntityManager;
use App\Util\ExceptionHelper;
use Doctrine\ORM\EntityManager;
use OldSound\RabbitMqBundle\RabbitMq\ConsumerInterface;
use PhpAmqpLib\Message\AMQPMessage;
use App\Service\Notification\EventDispatcher as NotificationEventDispatcher;
use Psr\Log\LoggerInterface;

class DeviceBatteryConsumer implements ConsumerInterface
{
    private $em;
    private $notificationDispatcher;
    private $logger;
    private $envType;
    private $slaveEntityManager;

    public const QUEUES_NUMBER = 2; // should be equal to number of queues in `config/rabbitmq.yaml`
    public const ROUTING_KEY_PREFIX = 'battery_device_'; // should be equal to `routing_keys` of queues

    public function __construct(
        EntityManager $em,
        NotificationEventDispatcher $notificationDispatcher,
        LoggerInterface $logger,
        SlaveEntityManager $slaveEntityManager,
        string $envType = null
    ) {
        $this->em = $em;
        $this->notificationDispatcher = $notificationDispatcher;
        $this->logger = $logger;
        $this->envType = $envType;
        $this->slaveEntityManager = $slaveEntityManager;
    }

    /**
     * @param AMQPMessage $msg
     * @return mixed|void
     * @throws \Doctrine\DBAL\ConnectionException
     */
    public function execute(AMQPMessage $msg)
    {
        try {
            $message = json_decode($msg->getBody());
            if (!$message) {
                return;
            }

            $deviceId = $message->device_id;
            $trackerHistoryId = $message->tracker_history_id;

            $device = $this->em->getRepository(Device::class)->getDeviceWithVehicle($deviceId);
            if (!$device) {
                $this->em->clear();
                return;
            }

            /** @var TrackerHistory $trackerHistory */
            $trackerHistory = $this->em->getRepository(TrackerHistory::class)->find($trackerHistoryId);

            /** @var Event $event */
            $event = $this->slaveEntityManager->getRepository(Event::class)->getEventByName(Event::TRACKER_BATTERY_PERCENTAGE);

            if (!$trackerHistory || !$event || is_null($trackerHistory->getBatteryVoltagePercentage())) {
                $this->em->clear();
                return;
            }

            $notifications = $this->em->getRepository(Notification::class)->getTeamNotifications(
                $event,
                $device->getTeam(),
                $trackerHistory->getTs(),
                $trackerHistory
            );

            if (!$notifications) {
                $this->em->clear();
                return;
            }

            try {
                $this->notificationDispatcher->dispatch(Event::TRACKER_BATTERY_PERCENTAGE, $trackerHistory);
                $this->em->clear();
            } catch (\Throwable $e) {
                throw $e;
            }
        } catch (\Throwable $e) {
            $this->logger->error(ExceptionHelper::convertToJson($e));
        }
    }
}
