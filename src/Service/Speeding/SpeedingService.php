<?php

namespace App\Service\Speeding;

use App\Entity\BaseEntity;
use App\Entity\Device;
use App\Entity\Idling;
use App\Entity\Speeding;
use App\Entity\Tracker\TrackerHistory;
use App\Entity\Tracker\TrackerHistoryTemp;
use App\Entity\User;
use App\Entity\Vehicle;
use App\EntityManager\SlaveEntityManager;
use App\Enums\EntityFields;
use App\Service\BaseService;
use App\Service\Device\DeviceService;
use App\Service\Setting\SettingService;
use App\Service\Vehicle\VehicleService;
use App\Service\User\UserService;
use App\Util\ArrayHelper;
use App\Util\DateHelper;
use App\Util\MetricHelper;
use App\Util\RequestFilterResolver\RequestFilterResolver;
use Doctrine\ORM\EntityManager;
use Pagerfanta\Adapter\CallbackAdapter;
use Pagerfanta\Adapter\NullAdapter;
use Pagerfanta\Pagerfanta;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class SpeedingService extends BaseService
{
    public const CALCULATE_SPEEDING_BATCH_SIZE = 20;

    protected $translator;
    private $em;
    private $deviceService;
    private $validator;
    private $settingService;
    private $vehicleService;
    private $userService;
    private $emSlave;

    public const FIELDS_TO_CONVERT = ['startedAt', 'finishedAt'];
    public const FIELDS_IN_METRES = ['distance'];

    /**
     * @param TranslatorInterface $translator
     * @param EntityManager $em
     * @param DeviceService $deviceService
     * @param ValidatorInterface $validator
     * @param SettingService $settingService
     * @param VehicleService $vehicleService
     * @param UserService $userService
     */
    public function __construct(
        TranslatorInterface $translator,
        EntityManager $em,
        DeviceService $deviceService,
        ValidatorInterface $validator,
        SettingService $settingService,
        VehicleService $vehicleService,
        UserService $userService,
        SlaveEntityManager $emSlave
    ) {
        $this->translator = $translator;
        $this->em = $em;
        $this->deviceService = $deviceService;
        $this->validator = $validator;
        $this->settingService = $settingService;
        $this->vehicleService = $vehicleService;
        $this->userService = $userService;
        $this->emSlave = $emSlave;
    }

    public function calculateSpeeding(int $deviceId, $minTS, $maxTS): void
    {
        /** @var Device $device */
        $device = $this->em->getReference(Device::class, $deviceId);
        $vehicle = $device->getVehicle();

        if ($vehicle && $this->settingService->isEcoSpeedEnabled($vehicle)) {
            $newestSecondSpeedingTS = $this->em->getRepository(Speeding::class)
                ->getNewestSecondSpeedingTSWithTypeFromDate($deviceId, $maxTS);
            $newestRecords = $this->em->getRepository(TrackerHistory::class)
                ->getTrackerRecordsByDeviceQuery($deviceId, $minTS, $newestSecondSpeedingTS);
            $ecoSpeedValue = $this->settingService->getEcoSpeedValue($vehicle);
            $previousTrackerHistory = null;
            $this->em->getRepository(Speeding::class)
                ->removeNewestSpeedingFromDate($deviceId, $minTS, $newestSecondSpeedingTS);

            if ($newestRecords->iterate()->next()) {
                $newestRecords = $newestRecords->iterate();
                $i = 1;

                /** @var TrackerHistory $newestTrackerHistory */
                foreach ($newestRecords as $row) {
                    $newestTrackerHistory = $row[0];
                    $this->handleSpeeding(
                        $device,
                        $newestTrackerHistory,
                        $previousTrackerHistory,
                        $ecoSpeedValue
                    );
                    $newestTrackerHistory->setIsCalculatedSpeeding(true);
                    $previousTrackerHistory = $newestTrackerHistory;

                    $newestTrackerHistoryTemp = $this->em->getRepository(TrackerHistoryTemp::class)->getTHTempByTH($newestTrackerHistory);
                    if ($newestTrackerHistoryTemp) {
                        $newestTrackerHistoryTemp->setIsCalculatedSpeeding(true);
                    }
                    if (($i % self::CALCULATE_SPEEDING_BATCH_SIZE) === 0) {
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
    }

    /**
     * @param int $deviceId
     * @param $minTS
     * @param $maxTS
     * @throws \Doctrine\DBAL\ConnectionException
     * @throws \Doctrine\ORM\NonUniqueResultException
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \Doctrine\Persistence\Mapping\MappingException
     */
    public function recalculateSpeeding(int $deviceId, $minTS, $maxTS): void
    {
        $this->calculateSpeeding($deviceId, $minTS, $maxTS);
    }

    /**
     * @param Device $device
     * @param TrackerHistory $trackerHistory
     * @param TrackerHistory|null $previousTrackerHistory
     * @param $ecoSpeedValue
     * @return Idling|null
     * @throws \Doctrine\ORM\NonUniqueResultException
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function handleSpeeding(
        Device $device,
        TrackerHistory $trackerHistory,
        ?TrackerHistory $previousTrackerHistory,
        $ecoSpeedValue
    ): ?Speeding {
        $trackerSpeed = $trackerHistory->getSpeed();
        $lastSpeeding = $this->em->getRepository(Speeding::class)
            ->getLastSpeedingStartedFromDate($device->getId(), $trackerHistory->getTs());

        if (!$lastSpeeding && $trackerSpeed > $ecoSpeedValue) {
            $speeding = $this->saveSpeedingEntity($device, $trackerHistory, $ecoSpeedValue);
        } else {
            $lastTrackerHistory = $previousTrackerHistory
                ?: $this->em->getRepository(TrackerHistory::class)->getPreviousTrackerHistory($trackerHistory);

            if ($lastSpeeding && $lastTrackerHistory) {
                $speeding = $this->handleLastPoint(
                    $device,
                    $trackerHistory,
                    $lastSpeeding,
                    $lastTrackerHistory,
                    $ecoSpeedValue
                );
            }
        }

        return $speeding ?? null;
    }

    /**
     * @param Request $request
     * @param User $user
     *
     * @return Pagerfanta
     */
    public function getPaginatedSpeedingsGroupedByDriver(Request $request, User $user): Pagerfanta
    {
        $requestParams = $request->query->all();
        $resolvedParams = RequestFilterResolver::resolve($requestParams);
        $params = $resolvedParams + $requestParams;
        $users = $this->userService->getDrivers([], $user, false);

        if (!$users) {
            $pagerfanta = new Pagerfanta(new NullAdapter());
            $pagerfanta->setAllowOutOfRangePages(true);
            $pagerfanta->setMaxPerPage($params['limit'] ?? 10);
            $pagerfanta->setCurrentPage($params['page'] ?? 1);

            return $pagerfanta;
        }

        $params['users'] = $users;

        $nbResultsCallback = function () use ($params, $user) {
            return $this->getTotalOfSpeedingsGroupedByDriver($params, $user);
        };
        $sliceCallback = function ($offset, $limit) use ($params, $user) {
            $params['offset'] = $offset;
            $params['limit'] = $limit;

            return $this->getSpeedingsGroupedByDriver($params, $user);
        };

        $pagerfanta = new Pagerfanta(new CallbackAdapter($nbResultsCallback, $sliceCallback));
        $pagerfanta->setAllowOutOfRangePages(true);
        $pagerfanta->setMaxPerPage($request->query->get('limit', 10));
        $pagerfanta->setCurrentPage($request->query->get('page', 1));

        return $pagerfanta;
    }

    /**
     * @param Request $request
     * @param User $user
     *
     * @return Pagerfanta
     */
    public function getPaginatedGroupedVehicles(Request $request, User $user): Pagerfanta
    {
        $requestParams = $request->query->all();
        $resolvedParams = RequestFilterResolver::resolve($requestParams);
        $params = $resolvedParams + $requestParams;

        $vehicles = $this->vehicleService->vehicleList($this->getVehicleElasticSearchParams($params), $user, false);

        if (!$vehicles) {
            $pagerfanta = new Pagerfanta(new NullAdapter());
            $pagerfanta->setAllowOutOfRangePages(true);
            $pagerfanta->setMaxPerPage($params['limit']);
            $pagerfanta->setCurrentPage($params['page']);

            return $pagerfanta;
        }

        $params['vehicles'] = $vehicles;

        $nbResultsCallback = function () use ($params) {
            return $this->getTotalOfGroupedVehicles($params);
        };
        $sliceCallback = function ($offset, $limit) use ($params) {
            $params['offset'] = $offset;
            $params['limit'] = $limit;

            return $this->getGroupedVehicles($params);
        };

        $pagerfanta = new Pagerfanta(new CallbackAdapter($nbResultsCallback, $sliceCallback));
        $pagerfanta->setAllowOutOfRangePages(true);
        $pagerfanta->setMaxPerPage($request->query->get('limit', 10));
        $pagerfanta->setCurrentPage($request->query->get('page', 1));

        return $pagerfanta;
    }

    /**
     * @param Request $request
     * @param Vehicle $vehicle
     *
     * @return Pagerfanta
     */
    public function getPaginatedSpeedingByVehicle(Request $request, Vehicle $vehicle): Pagerfanta
    {
        $requestParams = $request->query->all();
        $resolvedParams = RequestFilterResolver::resolve($requestParams);
        $params = $resolvedParams + $requestParams;

        $params['vehicleId'] = $vehicle->getId();

        $nbResultsCallback = function () use ($params) {
            return $this->getTotalSpeedingByVehicle($params);
        };
        $sliceCallback = function ($offset, $limit) use ($params) {
            $params['offset'] = $offset;
            $params['limit'] = $limit;

            return $this->getSpeedingByVehicle($params);
        };

        $pagerfanta = new Pagerfanta(new CallbackAdapter($nbResultsCallback, $sliceCallback));
        $pagerfanta->setAllowOutOfRangePages(true);
        $pagerfanta->setMaxPerPage($request->query->get('limit', 10));
        $pagerfanta->setCurrentPage($request->query->get('page', 1));

        return $pagerfanta;
    }

    /**
     * @param Request $request
     * @param User $user
     *
     * @param User $currentUser
     * @return Pagerfanta
     */
    public function getPaginatedSpeedingByDriver(Request $request, User $user, User $currentUser): Pagerfanta
    {
        $requestParams = $request->query->all();
        $resolvedParams = RequestFilterResolver::resolve($requestParams);

        $params = $resolvedParams + $requestParams;
        $params['driverId'] = $user->getId();

        $nbResultsCallback = function () use ($params, $currentUser) {
            return $this->getTotalOfSpeedingByDriver($params, $currentUser);
        };
        $sliceCallback = function ($offset, $limit) use ($params, $currentUser) {
            $params['offset'] = $offset;
            $params['limit'] = $limit;

            return $this->getSpeedingByDriver($params, $currentUser);
        };

        $pagerfanta = new Pagerfanta(new CallbackAdapter($nbResultsCallback, $sliceCallback));
        $pagerfanta->setAllowOutOfRangePages(true);
        $pagerfanta->setMaxPerPage($request->query->get('limit', 10));
        $pagerfanta->setCurrentPage($request->query->get('page', 1));

        return $pagerfanta;
    }

    /**
     * @param array $params
     *
     * @param User $user
     * @return array
     */
    public function getSpeedingByDriver(array $params, User $user): array
    {
        return $this->em->getRepository(Speeding::class)->getSpeedingByDriver($params, $user);
    }

    /**
     * @param array $params
     *
     * @param User $user
     * @return int
     */
    public function getTotalOfSpeedingByDriver(array $params, User $user): int
    {
        return $this->em->getRepository(Speeding::class)->getTotalOfSpeedingByDriver($params, $user);
    }

    /**
     * @param array $params
     *
     * @return int
     */
    public function getTotalSpeedingByVehicle(array $params): int
    {
        return $this->em->getRepository(Speeding::class)->getTotalOfSpeedingByVehicle($params);
    }

    /**
     * @param array $params
     *
     * @return array
     */
    public function getSpeedingByVehicle(array $params): array
    {
        $result = $this->em->getRepository(Speeding::class)->getSpeedingByVehicle($params);

        foreach ($result as &$item) {
            $item['startedAt'] = DateHelper::formatDate($item['startedAt']);
            $item['finishedAt'] = DateHelper::formatDate($item['finishedAt']);
        }

        return $result;
    }

    /**
     * @param array $params
     *
     * @return array
     */
    public function getGroupedVehicles(array $params): array
    {
        return $this->em->getRepository(Speeding::class)->getGroupedVehicles($params);
    }

    /**
     * @param array $params
     *
     * @return int
     */
    public function getTotalOfGroupedVehicles(array $params): int
    {
        return $this->em->getRepository(Speeding::class)->getTotalOfGroupedVehicles($params);
    }

    /**
     * @param array $params
     *
     * @param User $user
     * @return array
     */
    public function getSpeedingsGroupedByDriver(array $params, User $user): array
    {
        return $this->em->getRepository(Speeding::class)->getSpeedingsGroupedByDriver($params, $user);
    }

    /**
     * @param array $params
     *
     * @param User $user
     * @return int
     */
    public function getTotalOfSpeedingsGroupedByDriver(array $params, User $user): int
    {
        return $this->em->getRepository(Speeding::class)->getTotalOfSpeedingsGroupedByDriver($params, $user);
    }

    /**
     * @param array $params
     *
     * @return array
     */
    private function getVehicleElasticSearchParams(array $params): array
    {
        return array_intersect_key($params, array_flip(['defaultLabel', 'regNo', 'depot', 'groups']));
    }

    /**
     * @param Device $device
     * @param TrackerHistory $firstPoint
     * @param int $ecoSpeedValue
     * @param TrackerHistory|null $lastPoint
     * @return Speeding
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    protected function saveSpeedingEntity(
        Device $device,
        TrackerHistory $firstPoint,
        int $ecoSpeedValue,
        ?TrackerHistory $lastPoint = null
    ): Speeding {
        $speeding = new Speeding();
        $speeding->setDevice($device);
        $speeding->setPointStart($firstPoint);
        $speeding->setStartedAt($firstPoint->getTs());
        $speeding->setDriver($device->getVehicle() ? $device->getVehicle()->getDriver() : null);
        $speeding->setVehicle($device->getVehicle());
        $speeding->setAvgSpeed($firstPoint->getSpeed());
        $speeding->setMaxSpeed($firstPoint->getSpeed());
        $speeding->setEcoSpeed($ecoSpeedValue);

        if ($lastPoint) {
            $speeding->setPointFinish($lastPoint);
            $speeding->setFinishedAt($lastPoint->getTs());
            $speeding->setDuration(
                $lastPoint->getTs()->getTimestamp()
                - $firstPoint->getTs()->getTimestamp()
            );

            if ($speeding->getPointFinish()->getOdometer() && $speeding->getPointStart()->getOdometer()) {
                $speeding->setDistance(
                    $speeding->getPointFinish()->getOdometer() - $speeding->getPointStart()->getOdometer()
                );
            }
        }

        $this->em->persist($speeding);
        $this->em->flush();

        return $speeding;
    }

    /**
     * @param Device $device
     * @param TrackerHistory $trackerHistory
     * @param Speeding $lastSpeeding
     * @param TrackerHistory $lastTrackerHistory
     * @param $ecoSpeedValue
     * @return Speeding
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    protected function handleLastPoint(
        Device $device,
        TrackerHistory $trackerHistory,
        Speeding $lastSpeeding,
        TrackerHistory $lastTrackerHistory,
        $ecoSpeedValue
    ): Speeding {
        $lastSpeedingPoint = $lastSpeeding->getLastPoint();

        if ($lastTrackerHistory->getId() == $lastSpeedingPoint->getId()) {
            $lastSpeeding = $this->handleLastSpeeding(
                $trackerHistory,
                $lastTrackerHistory,
                $lastSpeeding,
                $ecoSpeedValue
            );
        } elseif ($lastTrackerHistory->getSpeed() > $ecoSpeedValue) {
            $speeding = $this->saveSpeedingEntity(
                $device,
                $lastTrackerHistory,
                $ecoSpeedValue,
                $trackerHistory
            );
        }

        return $speeding ?? $lastSpeeding;
    }

    /**
     * @param TrackerHistory $trackerHistory
     * @param TrackerHistory $lastTrackerHistory
     * @param Speeding $lastSpeeding
     * @param $ecoSpeedValue
     * @return Speeding
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    protected function handleLastSpeeding(
        TrackerHistory $trackerHistory,
        TrackerHistory $lastTrackerHistory,
        Speeding $lastSpeeding,
        $ecoSpeedValue
    ): Speeding {
        if ($lastTrackerHistory->getSpeed() > $ecoSpeedValue) {
            $lastSpeeding->setPointFinish($trackerHistory);
            $lastSpeeding->setFinishedAt($trackerHistory->getTs());
            $this->em->flush();
        }

        return $lastSpeeding;
    }
}
