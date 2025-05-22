<?php

namespace App\Service\Device\DeviceIOQueue\Vendor;

use App\Entity\Notification\Event;
use App\Entity\Notification\Notification;
use App\Entity\Tracker\TrackerHistory;
use App\Entity\Tracker\TrackerHistoryIO;
use App\Entity\Tracker\TrackerHistoryIOLast;
use App\Entity\Tracker\TrackerIOType;
use App\Entity\Vehicle;
use App\Service\Notification\EventDispatcher as NotificationEventDispatcher;
use Doctrine\ORM\EntityManager;

class Topflytech extends BaseVendor
{
    /**
     * @param EntityManager $em
     * @param NotificationEventDispatcher $notificationDispatcher
     */
    public function __construct(EntityManager $em, NotificationEventDispatcher $notificationDispatcher)
    {
        $this->em = $em;
        $this->notificationDispatcher = $notificationDispatcher;
    }

    public function calc(int $deviceId, array $trackerHistories, Vehicle $vehicle): Vehicle
    {
        $types = $this->em->getRepository(TrackerIOType::class)->findAll();
        $ioTypes = [];
        foreach ($types as $type) {
            $ioTypes[$type->getName()] = $type;
        }

        $thioLastListByDevice = $this->em->getRepository(TrackerHistoryIOLast::class)->getByDeviceId($deviceId);
        $thioList = [];

        foreach ($thioLastListByDevice as $thio) {
            $thioList[$thio->getType()->getName()] = $thio;
        }

        /** @var TrackerHistory $trackerHistory */
        foreach ($trackerHistories as $trackerHistory) {
            $IOExtraData = $trackerHistory->getIOExtraData();

            if (!$IOExtraData) {
                continue;
            }

            foreach (TrackerIOType::getAllTypeNames() as $typeName) {
                if (!isset($IOExtraData[$typeName])) {
                    continue;
                }

                $type = $ioTypes[$typeName] ?? null;

                if (!$type) {
                    continue;
                }

                $typeValue = $IOExtraData[$typeName];
                $THIOLast = $thioList[$typeName] ?? null;
                $lastTHIO = $THIOLast
                    ? $THIOLast->getTrackerHistoryIO()
                    : $this->em->getRepository(TrackerHistoryIO::class)
                        ->getPreviousRecordByTsAndDeviceIdAndType($trackerHistory->getTs(), $deviceId, $type);

                if ($typeValue == 1 &&
                    (!$lastTHIO ||
                        ($lastTHIO &&
                            $lastTHIO->getOccurredAtOff() &&
                            $lastTHIO->getOccurredAtOff() < $trackerHistory->getTs()
                        )
                    )
                ) {
                    $newTHIO = new TrackerHistoryIO();
                    $newTHIO->fromTrackerHistory($trackerHistory);
                    $newTHIO->setType($type);
                    $newTHIO->setValueOn(1);
                    $this->em->persist($newTHIO);
                    $this->em->flush();

                    $this->checkDispatchNotificationEvent($newTHIO);
                } elseif ($lastTHIO && $typeValue == 0 && !$lastTHIO->getOccurredAtOff()) {
                    $lastTHIO->setTrackerHistoryOff($trackerHistory);
                    $lastTHIO->setOccurredAtOff($trackerHistory->getTs());
                    $lastTHIO->setValueOff(0);
                    $this->em->flush();

                    $this->checkDispatchNotificationEvent($lastTHIO);
                }
            }
        }

        return $vehicle;
    }

    /**
     * @param TrackerHistoryIO $trackerHistoryIO
     */
    public function checkDispatchNotificationEvent(TrackerHistoryIO $trackerHistoryIO)
    {
        $event = $this->em->getRepository(Event::class)->getEventByName(Event::SENSOR_IO_STATUS);

        $notification = $this->em->getRepository(Notification::class)
            ->getTeamNotifications(
                $event,
                $trackerHistoryIO->getDevice()->getTeam(),
                $trackerHistoryIO->getCreatedAt(),
                $trackerHistoryIO
            );

        if ($notification) {
            $this->notificationDispatcher->dispatch(Event::SENSOR_IO_STATUS, $trackerHistoryIO);
        }
    }
}
