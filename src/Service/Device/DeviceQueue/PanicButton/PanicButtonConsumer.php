<?php

namespace App\Service\Device\DeviceQueue\PanicButton;

use App\Entity\Device;
use App\Entity\EventLog\EventLog;
use App\Entity\Notification\Event;
use App\Entity\Notification\Notification;
use App\Entity\Tracker\TrackerHistory;
use App\Entity\Tracker\TrackerHistoryLast;
use App\Util\ExceptionHelper;
use Doctrine\ORM\EntityManager;
use OldSound\RabbitMqBundle\RabbitMq\ConsumerInterface;
use PhpAmqpLib\Message\AMQPMessage;
use App\Service\Notification\EventDispatcher as NotificationEventDispatcher;
use Psr\Log\LoggerInterface;

class PanicButtonConsumer implements ConsumerInterface
{
    private $em;
    private $notificationDispatcher;
    private $logger;
    private $envType;

    /**
     * PanicButtonConsumer constructor.
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
            $trackerHistoryId = $message->tracker_history_id;

            $device = $this->em->getRepository(Device::class)->getDeviceWithVehicle($deviceId);
            if (!$device) {
                return;
            }

            /** @var  TrackerHistory $trackerHistory */
            $trackerHistory = $this->em->getRepository(TrackerHistory::class)->find($trackerHistoryId);
            if (!$trackerHistory) {
                $this->em->clear();
                return;
            }

            $event = $this->em->getRepository(Event::class)->findOneBy(['name' => Event::PANIC_BUTTON]);

//            $notification = $this->em->getRepository(Notification::class)
//                ->getTeamNotifications($event, $device->getTeam(), $trackerHistory->getTs(), $trackerHistory);


            if (!$event) {
                $this->em->clear();
                return;
            }

            $ts = clone $trackerHistory->getCreatedAt();
            $vehicleId = $device->getVehicle() ? $device->getVehicle()->getId() : null;

            if ($vehicleId) {
                $eventLogs = $this->em->getRepository(EventLog::class)->findEventLogByVehicleId(
                    $event,
                    $vehicleId,
                    $ts->sub(new \DateInterval('PT1M'))
                );
            } else {
                $eventLogs = $this->em->getRepository(EventLog::class)->findEventLogByDetailId(
                    $event,
                    $trackerHistory->getId()
                );
            }

            if (count($eventLogs)) {
                return;
            }

            $lat = $trackerHistory->getLat() ?? null;
            $lng = $trackerHistory->getLng() ?? null;

            if (!$lat && !$lng) {
                /** @var TrackerHistoryLast $lastTrackerHistory */
                $lastTrackerHistory = $device->getLastTrackerRecord() ?? null;
                if ($lastTrackerHistory) {
                    $lat = $lastTrackerHistory->getLat() ?? null;
                    $lng = $lastTrackerHistory->getLng() ?? null;
                    if ($lat && $lng) {
                        $context = [
                            EventLog::LAT => $lat,
                            EventLog::LNG => $lng,
                        ];
                    }
                }
            }

            $this->notificationDispatcher->dispatch(
                Event::PANIC_BUTTON,
                $trackerHistory,
                $trackerHistory->getTs(),
                $context ?? []
            );

//            if ($this->envType !== 'test') {
//                $this->em->getConnection()->close();
//            }
            $this->em->clear();
        } catch (\Throwable $e) {
            $this->logger->error(ExceptionHelper::convertToJson($e));
            return false;
        }
    }
}
