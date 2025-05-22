<?php

namespace App\Service\Idling;


use App\Entity\Device;
use App\Entity\EventLog\EventLog;
use App\Entity\Idling;
use App\Entity\Notification\Event;
use App\Entity\Notification\Notification;
use App\Entity\Tracker\TrackerHistory;
use App\Entity\Tracker\TrackerHistoryTemp;
use App\Service\BaseService;
use App\Service\Notification\EventDispatcher as NotificationEventDispatcher;
use App\Service\Notification\ScopeService;
use Carbon\Carbon;
use Doctrine\ORM\EntityManager;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class IdlingService extends BaseService
{
    public const CALCULATE_IDLING_BATCH_SIZE = 20;

    protected $translator;
    private $em;
    private $notificationDispatcher;

    public function __construct(
        TranslatorInterface $translator,
        EntityManager $em,
        NotificationEventDispatcher $notificationDispatcher,
        private readonly ScopeService $scopeService
    ) {
        $this->translator = $translator;
        $this->em = $em;
        $this->notificationDispatcher = $notificationDispatcher;
    }

    /**
     * @param Idling $idling
     * @return bool
     */
    private function isIdlingValidToTriggerEvent(Idling $idling): bool
    {
        $idlingLastDT = $idling->getFinishedAt() ?: $idling->getStartedAt();
        $tsDiff = $idling->getCreatedAt()->getTimestamp() - $idlingLastDT->getTimestamp();

        return $tsDiff < (60 * 60 * 24);
    }

    public function calculateIdling(int $deviceId, $minTS, $maxTS): void
    {
        $newestSecondIdlingTS = $this->em->getRepository(Idling::class)
            ->getNewestSecondIdlingTSWithTypeFromDate($deviceId, $maxTS);
        $newestRecords = $this->em->getRepository(TrackerHistory::class)
            ->getTrackerRecordsByDeviceQuery($deviceId, $minTS, $newestSecondIdlingTS);
        $device = $this->em->getReference(Device::class, $deviceId);
        $previousTrackerHistory = null;
        $this->em->getRepository(Idling::class)
            ->removeNewestIdlingFromDate($deviceId, $minTS, $newestSecondIdlingTS);

        if ($newestRecords->iterate()->next()) {
            $newestRecords = $newestRecords->iterate();
            $i = 1;

            /** @var TrackerHistory $newestTrackerHistory */
            foreach ($newestRecords as $row) {
                $newestTrackerHistory = $row[0];
                $this->handleIdling(
                    $device,
                    $newestTrackerHistory,
                    $previousTrackerHistory
                );
                $newestTrackerHistory->setIsCalculatedIdling(true);
                $previousTrackerHistory = $newestTrackerHistory;

                $newestTrackerHistoryTemp = $this->em->getRepository(TrackerHistoryTemp::class)->getTHTempByTH($newestTrackerHistory);
                if ($newestTrackerHistoryTemp) {
                    $newestTrackerHistoryTemp->setIsCalculatedIdling(true);
                }
                if (($i % self::CALCULATE_IDLING_BATCH_SIZE) === 0) {
                    $this->em->flush(); // Executes all updates.
                    $this->em->clear(); // Detaches all objects from Doctrine!
                    $device = $this->em->getReference(Device::class, $deviceId);
                    $previousTrackerHistory = null;
                }
                ++$i;
            }

            $this->em->flush();
        }

        $this->em->clear();
    }

    public function recalculateIdling(int $deviceId, $minTS, $maxTS): void
    {
        $this->calculateIdling($deviceId, $minTS, $maxTS);
    }

    public function handleIdling(
        Device $device,
        TrackerHistory $trackerHistory,
        ?TrackerHistory $previousTrackerHistory
    ): ?Idling {
        $deviceStatus = $trackerHistory->getDeviceStatusByIgnitionAndMovement(
            $trackerHistory->getIgnition(),
            $trackerHistory->getMovement()
        );
        $lastIdling = $this->em->getRepository(Idling::class)
            ->getLastIdlingStartedFromDate($device->getId(), $trackerHistory->getTs());

        if (!$lastIdling && $deviceStatus == Device::STATUS_IDLE) {
            $idling = $this->saveIdlingEntity($device, $trackerHistory);
        } else {
            $lastTrackerHistory = $previousTrackerHistory
                ?: $this->em->getRepository(TrackerHistory::class)->getPreviousTrackerHistory($trackerHistory);

            if ($lastIdling && $lastTrackerHistory) {
                $idling = $this->handleLastPoint($device, $trackerHistory, $lastIdling, $lastTrackerHistory);
            }
        }

        return $idling ?? null;
    }

    protected function saveIdlingEntity(
        Device $device,
        TrackerHistory $firstPoint,
        ?TrackerHistory $lastPoint = null
    ): Idling {
        $idling = new Idling();
        $idling->setDevice($device);
        $idling->setPointStart($firstPoint);
        $idling->setStartedAt($firstPoint->getTs());
        $idling->setDriver($device->getVehicle() ? $device->getVehicle()->getDriver() : null);
        $idling->setVehicle($device->getVehicle());

        if ($lastPoint) {
            $idling->setPointFinish($lastPoint);
            $idling->setFinishedAt($lastPoint->getTs());
            $idling->setDuration($lastPoint->getTs()->getTimestamp() - $firstPoint->getTs()->getTimestamp());
        }

        $this->em->persist($idling);
        $this->em->flush();

        return $idling;
    }

    protected function handleLastPoint(
        Device $device,
        TrackerHistory $trackerHistory,
        Idling $lastIdling,
        TrackerHistory $lastTrackerHistory
    ): Idling {
        $lastIdlingPoint = $lastIdling->getLastPoint();
        $lastDeviceStatus = $lastTrackerHistory->getDeviceStatusByIgnitionAndMovement(
            $lastTrackerHistory->getIgnition(),
            $lastTrackerHistory->getMovement()
        );

        if ($lastTrackerHistory->getId() == $lastIdlingPoint->getId()) {
            $lastIdling = $this->handleLastIdling($trackerHistory, $lastIdling, $lastDeviceStatus);
        } else {
            if ($lastDeviceStatus == Device::STATUS_IDLE) {
                $idling = $this->saveIdlingEntity($device, $lastTrackerHistory, $trackerHistory);
            }
        }

        return $idling ?? $lastIdling;
    }

    protected function handleLastIdling(
        TrackerHistory $trackerHistory,
        Idling $lastIdling,
        string $lastDeviceStatus
    ): Idling {
        if ($lastDeviceStatus == Device::STATUS_IDLE) {
            $lastIdling->setPointFinish($trackerHistory);
            $lastIdling->setFinishedAt($trackerHistory->getTs());
            $this->em->flush();
        }

        return $lastIdling;
    }

    public function excessingIdlingEvent(int $deviceId, $dateFrom, $dateTo)
    {
        /** @var Device $device */
        $device = $this->em->getRepository(Device::class)->find($deviceId);

        /** @var Event $event */
        $event = $this->em->getRepository(Event::class)->findOneBy(['name' => Event::VEHICLE_EXCESSING_IDLING]);

        $idlings = $this->em->getRepository(Idling::class)
            ->getIdlingByDeviceAndDateAndDuration($device, $dateFrom, $dateTo);

        /** @var Idling $idling */
        foreach ($idlings as $idling) {
            if (!$this->isIdlingValidToTriggerEvent($idling)) {
                continue;
            }

            $notifications = $this->em->getRepository(Notification::class)->getTeamNotifications(
                $event, $device->getTeam(), Carbon::parse($dateFrom), $idling, ['duration' => $idling->getDuration()]
            );

            if (!$notifications) {
                $this->em->clear();
                return;
            }

            // getting a list of notifications for the entity by received device
            $notifications = $this->scopeService->filterNotifications(
                $notifications,
                $idling,
                [
                    EventLog::LAT => $idling->getPointStart()->getLat(),
                    EventLog::LNG => $idling->getPointStart()->getLng()
                ]
            );

            if (!$notifications) {
                continue;
            }

            $eventLogs = $this->em->getRepository(EventLog::class)->findEventLogByDetailId(
                $event,
                $idling->getId()
            );

            if (!count($eventLogs)) {
                $context = [
                    EventLog::LAT => $idling->getPointStart()->getLat(),
                    EventLog::LNG => $idling->getPointStart()->getLng(),
                    EventLog::DURATION => $idling->getDuration(),
                ];
                $this->notificationDispatcher->dispatch(Event::VEHICLE_EXCESSING_IDLING, $idling, null, $context);
            }
        }
    }

}
