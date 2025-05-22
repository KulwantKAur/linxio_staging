<?php

namespace App\EventListener\ServiceRecord;

use App\Entity\Asset;
use App\Entity\Reminder;
use App\Entity\RepairData;
use App\Entity\ServiceRecord;
use App\Entity\Vehicle;
use Doctrine\ORM\Event\LifecycleEventArgs;
use FOS\ElasticaBundle\Persister\ObjectPersister;

class ServiceRecordEntityListener
{
    private $objectPersisterVehicle;
    private $objectPersisterAsset;

    /**
     * AreaHistoryEntityListener constructor.
     * @param ObjectPersister $objectPersisterVehicle
     * @param ObjectPersister $objectPersisterAsset
     */
    public function __construct(ObjectPersister $objectPersisterVehicle, ObjectPersister $objectPersisterAsset)
    {
        $this->objectPersisterVehicle = $objectPersisterVehicle;
        $this->objectPersisterAsset = $objectPersisterAsset;
    }

    /**
     * @param ServiceRecord $serviceRecord
     */
    public function postPersist(ServiceRecord $serviceRecord)
    {
        if ($serviceRecord->getReminder()) {
            $this->updateEntity($serviceRecord->getReminder()->addServiceRecord($serviceRecord));
        } else {
            $this->updateServiceRecordEntity($serviceRecord->getRepairData());
        }
    }

    /**
     * @param ServiceRecord $serviceRecord
     */
    public function postUpdate(ServiceRecord $serviceRecord)
    {
        if ($serviceRecord->getReminder()) {
            $this->updateEntity($serviceRecord->getReminder());
        } else {
            $this->updateServiceRecordEntity($serviceRecord->getRepairData());
        }
    }

    /**
     * @param ServiceRecord $serviceRecord
     */
    public function postRemove(ServiceRecord $serviceRecord)
    {
        if ($serviceRecord->getReminder()) {
            $this->updateEntity($serviceRecord->getReminder());
        } else {
            $this->updateServiceRecordEntity($serviceRecord->getRepairData());
        }
    }

    /**
     * @param Vehicle $vehicle
     */
    protected function updateVehicle(Vehicle $vehicle)
    {
        $this->objectPersisterVehicle->replaceOne($vehicle);
    }

    protected function updateAsset(Asset $asset)
    {
        $this->objectPersisterAsset->replaceOne($asset);
    }

    protected function updateEntity(Reminder $reminder)
    {
        if ($reminder->isVehicleReminder()) {
            $this->updateVehicle($reminder->getVehicle());
        } elseif ($reminder->isAssetReminder()) {
            $this->updateAsset($reminder->getAsset());
        }
    }

    protected function updateServiceRecordEntity(RepairData $repairData)
    {
        if ($repairData->isVehicleRepair()) {
            $this->updateVehicle($repairData->getVehicle());
        } elseif ($repairData->isAssetRepair()) {
            $this->updateAsset($repairData->getAsset());
        }
    }

    public function postLoad(ServiceRecord $serviceRecord, LifecycleEventArgs $args)
    {
    }
}