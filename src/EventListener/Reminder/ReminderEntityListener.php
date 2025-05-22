<?php

namespace App\EventListener\Reminder;

use App\Entity\Asset;
use App\Entity\Reminder;
use App\Entity\Vehicle;
use Doctrine\ORM\Event\LifecycleEventArgs;
use FOS\ElasticaBundle\Persister\ObjectPersister;

class ReminderEntityListener
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
     * @param Reminder $reminder
     */
    public function postPersist(Reminder $reminder)
    {
        $this->updateEntity($reminder);
    }

    /**
     * @param Reminder $reminder
     */
    public function postUpdate(Reminder $reminder)
    {
        $this->updateEntity($reminder);
    }

    /**
     * @param Reminder $reminder
     */
    public function postRemove(Reminder $reminder)
    {
        $this->updateEntity($reminder);
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

    public function postLoad(Reminder $reminder, LifecycleEventArgs $args)
    {
    }
}