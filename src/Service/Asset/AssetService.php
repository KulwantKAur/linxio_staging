<?php

namespace App\Service\Asset;

use App\Entity\Asset;
use App\Entity\Notification\Event;
use App\Entity\DeviceSensorType;
use App\Entity\Sensor;
use App\Entity\Team;
use App\Entity\TimeZone;
use App\Entity\Tracker\TrackerHistory;
use App\Entity\User;
use App\Entity\Vehicle;
use App\Events\Asset\AssetCreatedEvent;
use App\Events\Asset\AssetDeletedEvent;
use App\Events\Asset\AssetPairedWithSensorEvent;
use App\Events\Asset\AssetUnpairedWithSensorEvent;
use App\Events\Asset\AssetUpdatedEvent;
use App\Service\BaseService;
use App\Service\Client\ClientService;
use App\Service\ElasticSearch\ElasticSearch;
use App\Service\Notification\EventDispatcher as NotificationEventDispatcher;
use App\Service\Redis\MemoryDbService;
use App\Service\Route\RouteService;
use App\Service\User\UserService;
use App\Service\Validation\ValidationService;
use Carbon\Carbon;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManager;
use FOS\ElasticaBundle\Finder\TransformedFinder;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class AssetService extends BaseService
{
    use AssetFieldsTrait;

    protected $translator;
    private $em;
    private $assetFinder;
    private $eventDispatcher;
    private $validationService;
    private $validator;
    private $notificationDispatcher;
    private $userService;
    private $routeService;

    public const ELASTIC_NESTED_FIELDS = [];
    public const ELASTIC_SIMPLE_FIELDS = [
        'name' => 'name',
        'teamId' => 'team.id',
        'groups' => 'groups.id',
        'status' => 'status',
        'idNumber' => 'idNumber',
        'manufacturer' => 'manufacturer',
        'model' => 'model',
        'serialNumber' => 'serialNumber',
        'category' => 'category',
        'location' => 'location',
        'lastDataValue' => 'lastDataValue',
        'lastDataReceived' => 'lastOccurredAtBySensorHistory',
        'sensorBleId' => 'sensorBleId',
        'sensor' => 'sensorLabel',
        'sensorLabel' => 'sensorLabel',
        'isWithVehicle' => 'isWithVehicle',
    ];
    public const ELASTIC_RANGE_FIELDS = [];

    public const ELASTIC_FULL_SEARCH_FIELDS = [
        'name',
        'sensorBleId'
    ];

    public function __construct(
        TranslatorInterface $translator,
        EntityManager $em,
        TransformedFinder $assetFinder,
        EventDispatcherInterface $eventDispatcher,
        ValidationService $validationService,
        ValidatorInterface $validator,
        NotificationEventDispatcher $notificationDispatcher,
        UserService $userService,
        RouteService $routeService,
        private readonly MemoryDbService $memoryDbService
    ) {
        $this->translator = $translator;
        $this->em = $em;
        $this->assetFinder = new ElasticSearch($assetFinder);
        $this->eventDispatcher = $eventDispatcher;
        $this->validationService = $validationService;
        $this->validator = $validator;
        $this->notificationDispatcher = $notificationDispatcher;
        $this->userService = $userService;
        $this->routeService = $routeService;
    }

    /**
     * @param array $data
     * @param User $currentUser
     * @return Asset
     * @throws \Exception
     */
    public function create(array $data, User $currentUser): Asset
    {
        $this->validateAssetFields($data, $currentUser);
        $asset = new Asset($data);
        $asset->setCreatedBy($currentUser);

        if ($currentUser->isInClientTeam()) {
            $asset->setTeam($currentUser->getTeam());
        } else {
            $team = $data['teamId']
                ? $this->em->getRepository(Team::class)->find($data['teamId'])
                : $currentUser->getTeam();
            $asset->setTeam($team);
        }

        if ($data['sensor_id'] ?? null) {
            /** @var Sensor $sensor */
            $sensor = $this->em->getRepository(Sensor::class)->find($data['sensor_id']);
            $asset->setSensor($sensor);
            $asset->setLastTrackerHistorySensor($asset->getLastTrackerHistorySensor());
        }

        $asset = $this->handleDepotGroupsParams($data, $asset, $currentUser);

        $this->validate($this->validator, $asset);
        $this->em->persist($asset);
        $this->em->flush();

        if ($asset ?? null) {
            $this->eventDispatcher->dispatch(new AssetCreatedEvent($asset), AssetCreatedEvent::NAME);
            $this->notificationDispatcher->dispatch(Event::ASSET_CREATED, $asset);
        }

        return $asset;
    }

    /**
     * @param array $data
     * @param User $currentUser
     * @param Asset $asset
     * @return Asset
     * @throws \Exception
     */
    public function edit(array $data, User $currentUser, Asset $asset): Asset
    {
        $this->validateAssetFields($data, $currentUser);

        $asset = $this->handleDepotGroupsParams($data, $asset, $currentUser);
        $data['updatedAt'] = new \DateTime();

        $asset->setAttributes($data);
        $asset->setUpdatedBy($currentUser);

        $this->em->flush();

        $this->em->refresh($asset);

        if ($asset ?? null) {
            $this->eventDispatcher->dispatch(new AssetUpdatedEvent($asset), AssetUpdatedEvent::NAME);
        }

        return $asset;
    }

    /**
     * @param Asset $asset
     * @param User $currentUser
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function remove(Asset $asset, User $currentUser)
    {
        $asset->setStatus(Asset::STATUS_DELETED);
        $asset->setUpdatedAt(new \DateTime());
        $asset->setUpdatedBy($currentUser);

        $this->em->flush();

        $this->eventDispatcher->dispatch(new AssetDeletedEvent($asset), AssetDeletedEvent::NAME);
        $this->notificationDispatcher->dispatch(Event::ASSET_DELETED, $asset);
    }

    /**
     * @param Asset $asset
     * @param ArrayCollection $groups
     * @param User $currentUser
     *
     * @return Asset
     */
    private function addAssetToGroups(Asset $asset, ArrayCollection $groups, User $currentUser)
    {
        $areaGroups = $asset->getGroups();
        foreach ($areaGroups as $areaGroup) {
            if (!$groups->contains($areaGroup)) {
                $areaGroup->removeAsset($asset);
                $asset->removeFromGroup($areaGroup);
            }
        }
        if ($groups->count()) {
            foreach ($groups as $group) {
                if (!$asset->getGroups()->contains($group)
                    && ClientService::checkTeamAccess($group->getTeam(), $currentUser)
                ) {
                    $group->addAsset($asset);
                    $asset->addToGroup($group); //hack only for response, does not impact to entity database data
                }
            }
        } elseif ($groups->count() === 0) {
            foreach ($areaGroups as $areaGroup) {
                $areaGroup->removeAsset($asset);
                $asset->removeFromGroup($areaGroup);
            }
        }

        return $asset;
    }

    /**
     * @param int $id
     * @param User $user
     * @return Asset|null
     */
    public function getById(int $id, User $user): ?Asset
    {
        $asset = null;

        if ($user->isInAdminTeam()) {
            $asset = $this->em->getRepository(Asset::class)->find($id);
        } elseif ($user->isInClientTeam()) {
            $asset = $this->em->getRepository(Asset::class)->findOneBy(
                [
                    'id' => $id,
                    'team' => $user->getTeam()
                ]
            );
        }

        return $asset;
    }

    /**
     * @param array $params
     * @param User $user
     * @param bool $paginated
     * @param string[] $defaultFields
     * @return array
     * @throws \Elastica\Exception\ElasticsearchException
     */
    public function assetList(
        array $params,
        User $user,
        bool $paginated = true,
        $defaultFields = Asset::DEFAULT_DISPLAY_VALUES
    ) {
        if ($user->isInClientTeam() || $user->isInResellerTeam()) {
            $params['teamId'] = $user->getTeam()->getId();
        }
        if ($user->isClientManager() && !$user->isAllTeamsPermissions()) {
            $params['teamId'] = $user->getManagedTeamsIds();
        }

        $params['fields'] = array_merge($defaultFields, $params['fields'] ?? []);
        $params = self::handleStatusParams($params);
        $fields = $this->prepareElasticFields($params);

        return $this->assetFinder->find($fields, $fields['_source'] ?? [], $paginated);
    }

    /**
     * @param Asset $asset
     * @param Sensor $sensor
     * @param User $currentUser
     * @return Asset
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function pairWithSensor(Asset $asset, Sensor $sensor, User $currentUser): Asset
    {
        if (!$sensor->isTypeWithTemperature()) {
            throw new AccessDeniedHttpException(
                $this->translator->trans(
                    'entities.asset.asset_cannot_be_paired_with_type', ['%type%' => $sensor->getTypeName()]
                )
            );
        }

        $asset->setSensor($sensor);
        $asset->setUpdatedBy($currentUser);
        $asset->setUpdatedAt(new \DateTime());
        $asset->setLastTrackerHistorySensor($asset->getLastTrackerHistorySensor());
        $this->validate($this->validator, $asset);
        $this->em->flush();
        $this->eventDispatcher->dispatch(new AssetUpdatedEvent($asset), AssetUpdatedEvent::NAME);
        $this->eventDispatcher->dispatch(
            new AssetPairedWithSensorEvent($asset, $sensor),
            AssetPairedWithSensorEvent::NAME
        );

        return $asset;
    }

    /**
     * @param Asset $asset
     * @param Sensor $sensor
     * @param User $currentUser
     * @return Asset
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function unpairWithSensor(Asset $asset, Sensor $sensor, User $currentUser): Asset
    {
        $asset->setSensor(null);
        $asset->setUpdatedBy($currentUser);
        $asset->setUpdatedAt(new \DateTime());
        $this->em->flush();
        $this->eventDispatcher->dispatch(new AssetUpdatedEvent($asset), AssetUpdatedEvent::NAME);
        $this->eventDispatcher
            ->dispatch(new AssetUnpairedWithSensorEvent($asset, $sensor), AssetUnpairedWithSensorEvent::NAME);

        return $asset;
    }

    /**
     * @param Asset $asset
     * @param User $currentUser
     * @return Asset
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function restore(Asset $asset, User $currentUser)
    {
        if ($asset->getStatus() === Asset::STATUS_DELETED) {
            $asset->setStatus(Asset::STATUS_OK);
            $asset->setUpdatedAt(new \DateTime());
            $asset->setUpdatedBy($currentUser);

            $this->em->flush();

            $this->eventDispatcher->dispatch(new AssetUpdatedEvent($asset), AssetUpdatedEvent::NAME);
        }

        return $asset;
    }

    public function getDailyData(Asset $asset, string $timezone = null): array
    {
        $currentUser = $this->userService->getLoggedUser();
        $timezone = $timezone ?? ($currentUser && $currentUser->getTimezone()
            ? $currentUser->getTimezone()
            : TimeZone::DEFAULT_TIMEZONE['name']);
        $date = Carbon::now()->setTimezone($timezone);
        $todayFrom = (clone $date)->startOfDay();
        $todayTo = (clone $date)->endOfDay();
        $assetSensorHistories = $asset->getAssetSensorHistoriesByRange($todayFrom, $todayTo);
        $distance = 0;
        $duration = 0;
        $avgSpeed = 0;
        $avgSpeedPointCounter = 0;

        foreach ($assetSensorHistories as $assetSensorHistory) {
            $vehicle = $assetSensorHistory->getVehicleWithDeleted();
            $device = $assetSensorHistory->getDeviceWithDeleted();
            $dateFrom = $assetSensorHistory->getInstalledAt() > $todayFrom ? $assetSensorHistory->getInstalledAt() : $todayFrom;
            $dateTo = $assetSensorHistory->isUninstalled() && $assetSensorHistory->isUninstalled() < $todayTo
                ? $assetSensorHistory->getUninstalledAt()
                : $todayTo;

            // if last data received more then 1 day ago - return 0
            if (!$vehicle || !$device || !$device->getLastTrackerRecord()
                || ($device && $device->getLastTrackerRecord()
                    && $device->getLastTrackerRecord()->getTs() < (clone $date)->startOfDay()->setTimezone('UTC'))) {
                $distance += 0;
                $duration += 0;
                $avgSpeed += 0;
            } else {
//                $distance += $this->em->getRepository(Vehicle::class)
//                    ->getTotalOdometer($vehicle, $dateFrom->format('c'), $dateTo->format('c'));
//                $duration += $this->em->getRepository(Vehicle::class)
//                    ->getTotalDrivingTime($vehicle, $dateFrom->format('c'), $dateTo->format('c'));
//                $avgSpeed += $duration && $distance
//                    ? (($distance / 1000) / ($duration / 3600))
//                    : 0;
//                $avgSpeedPointCounter += ($duration && $distance) ? 1 : 0;

                $data = $this->routeService->getDriverOrVehicleRoutes($dateFrom, $dateTo, $currentUser, null, $vehicle);
                $distance += $data['distance'];
                $duration += $data['duration'];
                $avgSpeed += $data['avgSpeed'];
                $avgSpeedPointCounter++;
            }

        }

        return [
            'distance' => $distance,
            'duration' => $duration,
            'avgSpeed' => ($avgSpeed != 0 && $avgSpeedPointCounter != 0) ? $avgSpeed / $avgSpeedPointCounter : 0,
        ];
    }

    public function getTodayData(Asset $asset): array
    {
        $cacheData = $this->memoryDbService->get($asset->getTodayDataKey());

        return $cacheData['data'] ?? [
            'distance' => 0,
            'duration' => 0,
            'avgSpeed' => 0
        ];
    }
}
