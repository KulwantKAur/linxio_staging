<?php

namespace App\Service\Vehicle;

use App\Entity\Device;
use App\Entity\Notification\Event;
use App\Entity\Tracker\TrackerHistory;
use App\Entity\Vehicle;
use App\Entity\VehicleOdometer;
use App\Entity\User;
use App\Service\BaseService;
use Doctrine\ORM\EntityManager;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use App\Service\Notification\EventDispatcher as NotificationEventDispatcher;

class VehicleOdometerService extends BaseService
{
    protected $translator;
    private $em;
    private $validator;
    private $paginator;
    private $notificationDispatcher;
    private $vehicleService;

    public function __construct(
        TranslatorInterface $translator,
        EntityManager $em,
        ValidatorInterface $validator,
        PaginatorInterface $paginator,
        NotificationEventDispatcher $notificationDispatcher,
        VehicleService $vehicleService
    ) {
        $this->translator = $translator;
        $this->em = $em;
        $this->validator = $validator;
        $this->paginator = $paginator;
        $this->notificationDispatcher = $notificationDispatcher;
        $this->vehicleService = $vehicleService;
    }

    public function saveByVehicleAndDataAndUser(Vehicle $vehicle, array $data, User $user): VehicleOdometer
    {
        $vehicleOdometer = new VehicleOdometer($data);
        $vehicleOdometer->fromVehicle($vehicle);
        $device = $vehicle->getDevice();
        
        if ($device && ($data['occurredAt'] ?? null)) {
            $trackerHistory = $this->em->getRepository(TrackerHistory::class)
                ->getLastTrackerRecordByDeviceAndDate($device, $data['occurredAt']);
        } else {
            $trackerHistory = $device?->getLastTrackerRecord()?->getTrackerHistory();
        }

        $vehicleOdometer->fromTrackerRecord($trackerHistory);
        $vehicleOdometer->setCreatedBy($user);
        $prevOdometerValue = $this->getOdometerValueByVehicleAndOccurredAt(
            $vehicle,
            (clone ($vehicleOdometer->getOccurredAt()))->sub(new \DateInterval('PT1S'))->format('Y-m-d H:i:s.000')
        );
        $vehicleOdometer->setPrevOdometer($prevOdometerValue);

        $this->validate($this->validator, $vehicleOdometer);
        $this->em->persist($vehicleOdometer);
        $this->em->flush();
        $this->notificationDispatcher->dispatch(
            Event::ODOMETER_CORRECTED,
            $vehicleOdometer,
            null,
            ['oldValue' => $prevOdometerValue]
        );

        return $vehicleOdometer;
    }

    public function editByIdAndDataAndUser(int $vehicleOdometerId, array $data, User $user): VehicleOdometer
    {
        $vehicleOdometer = $this->em->getRepository(VehicleOdometer::class)->find($vehicleOdometerId);

        if ($vehicleOdometer) {
            $vehicleOdometer->setUpdatedBy($user);
            $vehicleOdometer->setUpdatedAt(new \DateTime());
            $vehicleOdometer->setOdometer($data['odometer'] ?? $vehicleOdometer->getOdometer());
            $vehicleOdometer->setOccurredAt(
                isset($data['occurredAt'])
                    ? BaseService::parseDateToUTC($data['occurredAt'])
                    : $vehicleOdometer->getOccurredAt()
            );
            $vehicleOdometer->setIsSyncedWithDevice(
                $data['syncedWithDevice'] ?? $vehicleOdometer->isSyncedWithDevice()
            );

            if ($vehicleOdometer->getOdometerFromDevice() > 0) {
                $vehicleOdometer->setAccuracy(
                    $vehicleOdometer->getOdometer() - $vehicleOdometer->getOdometerFromDevice()
                );
            }

            $this->validate($this->validator, $vehicleOdometer);
            $this->em->flush();
        }

        return $vehicleOdometer;
    }

    public function deleteById(int $vehicleOdometerId): ?VehicleOdometer
    {
        $vehicleOdometer = $this->em->getRepository(VehicleOdometer::class)->find($vehicleOdometerId);

        if ($vehicleOdometer) {
            $this->em->remove($vehicleOdometer);
            $this->em->flush();
        }

        return $vehicleOdometer;
    }

    public function listByDevice(Device $device, int $page, int $limit)
    {
        $query = $this->em->getRepository(VehicleOdometer::class)->queryAllByDevice($device);
        $pagination = $this->paginator->paginate($query, $page, $limit);
        $data = [];

        foreach ($pagination as $item) {
            $data[] = $item->toArray();
        }

        $pagination->setItems($data);

        return $pagination;
    }

    public function listByVehicle(Vehicle $vehicle, int $page, int $limit)
    {
        $query = $this->em->getRepository(VehicleOdometer::class)->queryAllByVehicle($vehicle);
        $pagination = $this->paginator->paginate($query, $page, $limit);
        $data = [];

        foreach ($pagination as $item) {
            $data[] = $item->toArray();
        }

        $pagination->setItems($data);

        return $pagination;
    }

    public function getById(int $vehicleOdometerId): ?VehicleOdometer
    {
        return $this->em->getRepository(VehicleOdometer::class)->find($vehicleOdometerId);
    }

    public function getOdometerDataForListByVehicleAndOccurredAt(Vehicle $vehicle, $occurredAt = null): ?VehicleOdometer
    {
        $occurredAt = $occurredAt ? BaseService::parseDateToUTC($occurredAt) : null;
        $lastTR = $this->vehicleService->getLastTrackerRecordByVehicle($vehicle, $occurredAt);
        $odometerFromTracker = $lastTR?->getOdometer();

        /** @var VehicleOdometer $vehicleOdometer */
        $vehicleOdometer = $this->em->getRepository(VehicleOdometer::class)
            ->lastByVehicleAndOccurredAt($vehicle, $occurredAt);

        if ($vehicleOdometer) {
            if ($odometerFromTracker && ($vehicleOdometer->getAccuracy() + $odometerFromTracker) > 0) {
                $vehicleOdometer->setOdometer($vehicleOdometer->getAccuracy() + $odometerFromTracker);
            }

            if ($lastTR) {
                $vehicleOdometer->setLastTrackerHistory($lastTR);
                $vehicleOdometer->setLastTrackerRecordOccurredAt(
                    max($lastTR->getTs(), $vehicleOdometer->getOccurredAt())
                );
            }
        } else {
            // create mock, don't persist
            $vehicleOdometer = new VehicleOdometer();
            $vehicleOdometer->setCreatedAt(null);
            $vehicleOdometer->setOccurredAt(null);
            $vehicleOdometer->fromVehicle($vehicle);

            if ($odometerFromTracker && $odometerFromTracker > 0) {
                $vehicleOdometer->setOdometer($odometerFromTracker);
            } else {
                return null;
            }

            if ($lastTR) {
                $vehicleOdometer->fromTrackerRecord($lastTR);
            }
        }

        return $vehicleOdometer;
    }

    /**
     * @param Vehicle $vehicle
     * @param $occurredAt
     * @return int|null
     * @throws \Doctrine\ORM\Exception\NotSupported
     */
    public function getOdometerValueByVehicleAndOccurredAt(Vehicle $vehicle, $occurredAt = null): ?int
    {
        $occurredAt = $occurredAt ? BaseService::parseDateToUTC($occurredAt) : null;
        $lastTR = $this->vehicleService->getLastTrackerRecordByVehicle($vehicle, $occurredAt);
        $odometerFromLastRecord = $lastTR?->getOdometer();
        $vehicleOdometer = $this->em->getRepository(VehicleOdometer::class)
            ->lastByVehicleAndOccurredAt($vehicle, $occurredAt);

        if ($vehicleOdometer) {
            $odometerValue = $vehicleOdometer->getOdometer();

            if ($odometerFromLastRecord && ($vehicleOdometer->getAccuracy() + $odometerFromLastRecord) > 0) {
                $odometerValue = $vehicleOdometer->getAccuracy() + $odometerFromLastRecord;
            }
        } else {
            $odometerValue = $odometerFromLastRecord;
        }

        return $odometerValue;
    }

    public function updateByTrackerHistory(
        VehicleOdometer $vehicleOdometer,
        TrackerHistory $trackerHistory
    ): VehicleOdometer {
        $vehicleOdometer->setUpdatedAt(new \DateTime());
        $vehicleOdometer->setOdometerFromDevice($trackerHistory->getOdometer());
        $vehicleOdometer->setLastTrackerHistory($trackerHistory);
        $vehicleOdometer->setLastTrackerRecordOccurredAt($trackerHistory->getTs());

        if ($vehicleOdometer->getOdometerFromDevice() > 0) {
            $vehicleOdometer->setAccuracy(
                $vehicleOdometer->getOdometer() - $vehicleOdometer->getOdometerFromDevice()
            );
        }

        $this->validate($this->validator, $vehicleOdometer);
        $this->em->flush();

        return $vehicleOdometer;
    }

    public function updateVehicleOdometer(Device $device, array $trackerHistoryIDs): void
    {
        $vehicle = $device->getVehicle();

        if (!$vehicle) {
            return;
        }

        $vehicleOdometer = $vehicle->getLastOdometerData();

        if (!$vehicleOdometer || $vehicleOdometer->getOdometerFromDevice()) {
            return;
        }

        $trackerHistory = $this->em->getRepository(TrackerHistory::class)
            ->getTrackerHistoryWithMaxOdometerByIds($trackerHistoryIDs);

        if ($trackerHistory) {
            $this->updateByTrackerHistory($vehicleOdometer, $trackerHistory);
        }
    }
}
