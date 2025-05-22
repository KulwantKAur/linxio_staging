<?php

namespace App\Service\Device\DeviceSensorQueue;

use App\Entity\Device;
use App\Entity\Notification\Event;
use App\Entity\Notification\Notification;
use App\Entity\Tracker\TrackerHistorySensor;
use App\Util\ExceptionHelper;
use Doctrine\ORM\EntityManager;
use OldSound\RabbitMqBundle\RabbitMq\ConsumerInterface;
use PhpAmqpLib\Message\AMQPMessage;
use App\Service\Notification\EventDispatcher as NotificationEventDispatcher;
use Psr\Log\LoggerInterface;

class DeviceSensorConsumer implements ConsumerInterface
{
    private $em;
    private $notificationDispatcher;
    private $logger;
    private $envType;

    /**
     * DeviceVoltageConsumer constructor.
     * @param EntityManager $em
     * @param NotificationEventDispatcher $notificationDispatcher
     * @param LoggerInterface $logger
     * @param string|null $envType
     */
    public function __construct(
        EntityManager $em,
        NotificationEventDispatcher $notificationDispatcher,
        LoggerInterface $logger,
        string $envType = null
    ) {
        $this->em = $em;
        $this->notificationDispatcher = $notificationDispatcher;
        $this->logger = $logger;
        $this->envType = $envType;
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
            $trackerHistorySensorId = $message->tracker_history_sensor_id;

            /** @var Device $device */
            $device = $this->em->getRepository(Device::class)->find($deviceId);
            /** @var TrackerHistorySensor $trackerHistorySensor */
            $trackerHistorySensor = $this->em->getRepository(TrackerHistorySensor::class)->find(
                $trackerHistorySensorId
            );

            if ($trackerHistorySensor) {
                /** @var TrackerHistorySensor $prevTrackerHistorySensor */
                $prevTrackerHistorySensor = $this->em->getRepository(TrackerHistorySensor::class)
                    ->getPrevTrackerHistorySensor($trackerHistorySensor, true);
                $this->callEventHandlers($prevTrackerHistorySensor, $trackerHistorySensor, $device);
            }

            if ($this->envType !== 'test') {
                $this->em->getConnection()->close();
            }
            $this->em->clear();
        } catch (\Throwable $e) {
            $this->logger->error(ExceptionHelper::convertToJson($e));
        }
    }

    private function callEventHandlers(
        ?TrackerHistorySensor $prevTrackerHistorySensor,
        TrackerHistorySensor $trackerHistorySensor,
        Device $device
    ) {
        /** @var Event $eventTemperature */
        $eventTemperature = $this->em->getRepository(Event::class)
            ->findOneBy(['name' => Event::SENSOR_TEMPERATURE]);
        /** @var Event $eventHumidity */
        $eventHumidity = $this->em->getRepository(Event::class)->getEventByName(Event::SENSOR_HUMIDITY);
        /** @var Event $eventLight */
        $eventLight = $this->em->getRepository(Event::class)->getEventByName(Event::SENSOR_LIGHT);
        /** @var Event $eventBatteryLevel */
        $eventBatteryLevel = $this->em->getRepository(Event::class)
            ->findOneBy(['name' => Event::SENSOR_BATTERY_LEVEL]);

        $this->handleSensorEvent($device, $trackerHistorySensor, $eventTemperature);

        $this->handleSensorEvent($device, $trackerHistorySensor, $eventHumidity);

        if (!$prevTrackerHistorySensor || ($prevTrackerHistorySensor &&
                $prevTrackerHistorySensor->getLight() !== $trackerHistorySensor->getLight())) {
            $this->handleSensorEvent($device, $trackerHistorySensor, $eventLight);
        }

        $this->handleSensorEvent($device, $trackerHistorySensor, $eventBatteryLevel);
    }

    private function handleSensorEvent(Device $device, TrackerHistorySensor $trackerHistorySensor, Event $event)
    {
        $notifications = $this->em->getRepository(Notification::class)->getTeamNotifications(
            $event,
            $device->getTeam(),
            $trackerHistorySensor->getOccurredAt(),
            $trackerHistorySensor
        );
        if (!$notifications) {
            return;
        }

        try {
            $this->notificationDispatcher->dispatch($event->getName(), $trackerHistorySensor);
        } catch (\Throwable $e) {
            $this->logger->error(ExceptionHelper::convertToJson($e));
        }
    }
}
