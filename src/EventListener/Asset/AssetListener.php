<?php

namespace App\EventListener\Asset;

use App\Entity\AssetSensorHistory;
use App\Entity\Vehicle;
use App\Enums\EntityHistoryTypes;
use App\Events\Asset\AssetCreatedEvent;
use App\Events\Asset\AssetDeletedEvent;
use App\Events\Asset\AssetPairedWithSensorEvent;
use App\Events\Asset\AssetUnpairedWithSensorEvent;
use App\Events\Asset\AssetUpdatedEvent;
use App\Service\EntityHistory\EntityHistoryService;
use Carbon\Carbon;
use Doctrine\ORM\EntityManager;
use FOS\ElasticaBundle\Persister\ObjectPersister;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class AssetListener implements EventSubscriberInterface
{

    private $em;
    private $entityHistoryService;
    private $vehicleObjectPersister;

    /**
     * AssetListener constructor.
     * @param EntityManager $em
     * @param EntityHistoryService $entityHistoryService
     * @param ObjectPersister $vehicleObjectPersister
     */
    public function __construct(
        EntityManager $em,
        EntityHistoryService $entityHistoryService,
        ObjectPersister $vehicleObjectPersister
    ) {
        $this->em = $em;
        $this->entityHistoryService = $entityHistoryService;
        $this->vehicleObjectPersister = $vehicleObjectPersister;
    }

    /**
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return [
            AssetCreatedEvent::NAME => 'onAssetCreated',
            AssetUpdatedEvent::NAME => 'onAssetUpdated',
            AssetDeletedEvent::NAME => 'onAssetDeleted',
            AssetPairedWithSensorEvent::NAME => 'onAssetPairedWithSensor',
            AssetUnpairedWithSensorEvent::NAME => 'onAssetUnpairedWithSensor',
        ];
    }

    /**
     * @param AssetUpdatedEvent $event
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function onAssetUpdated(AssetUpdatedEvent $event)
    {
        $asset = $event->getAsset();
        $this->entityHistoryService->create(
            $asset,
            $asset->getUpdatedAt() ? $asset->getUpdatedAt()->getTimestamp() : Carbon::now('UTC')->getTimestamp(),
            EntityHistoryTypes::ASSET_UPDATED,
            $asset->getUpdatedBy()
        );

        $this->em->flush();
    }

    /**
     * @param AssetDeletedEvent $event
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function onAssetDeleted(AssetDeletedEvent $event)
    {
        $asset = $event->getAsset();
        $this->entityHistoryService->create(
            $asset,
            time(),
            EntityHistoryTypes::ASSET_DELETED,
            $asset->getUpdatedBy()
        );

        $this->em->flush();
    }

    /**
     * @param AssetCreatedEvent $event
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function onAssetCreated(AssetCreatedEvent $event)
    {
        $asset = $event->getAsset();
        $this->entityHistoryService->create(
            $asset,
            $asset->getCreatedAt()->getTimestamp(),
            EntityHistoryTypes::ASSET_CREATED,
            $asset->getCreatedBy()
        );

        $this->em->flush();
    }

    /**
     * @param AssetPairedWithSensorEvent $event
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function onAssetPairedWithSensor(AssetPairedWithSensorEvent $event)
    {
        $asset = $event->getAsset();
        $sensor = $event->getSensor();

        $assetSensorHistory = $this->em->getRepository(AssetSensorHistory::class)
            ->getLastBySensorAndAsset($sensor, $asset);

        if (!$assetSensorHistory) {
            $assetSensorHistory = new AssetSensorHistory();
            $assetSensorHistory->setAsset($asset)
                ->setSensor($sensor);
            $this->em->persist($assetSensorHistory);
            $this->em->flush();
        }


        if ($sensor->getVehicle()) {
            $this->updateVehicle($sensor->getVehicle());
        }
    }

    /**
     * @param AssetUnpairedWithSensorEvent $event
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function onAssetUnpairedWithSensor(AssetUnpairedWithSensorEvent $event)
    {
        $asset = $event->getAsset();
        $sensor = $event->getSensor();
        $assetSensorHistory = $this->em->getRepository(AssetSensorHistory::class)
            ->getLastBySensorAndAsset($sensor, $asset);

        if ($assetSensorHistory) {
            $assetSensorHistory->setUninstalledAt(Carbon::now('UTC'));
        }

        $this->em->flush();

        if ($sensor->getVehicle()) {
            $this->updateVehicle($sensor->getVehicle());
        }
    }

    protected function updateVehicle(Vehicle $vehicle)
    {
        $this->vehicleObjectPersister->replaceOne($vehicle);
    }
}