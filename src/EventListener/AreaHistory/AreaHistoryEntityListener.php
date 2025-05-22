<?php

namespace App\EventListener\AreaHistory;

use App\Entity\AreaHistory;
use App\Entity\Vehicle;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Event\LifecycleEventArgs;
use FOS\ElasticaBundle\Persister\ObjectPersister;

class AreaHistoryEntityListener
{
    private $objectPersisterVehicle;
    private EntityManager $em;

    /**
     * AreaHistoryEntityListener constructor.
     */
    public function __construct(ObjectPersister $objectPersister, EntityManager $em)
    {
        $this->objectPersisterVehicle = $objectPersister;
        $this->em = $em;
    }

    public function postPersist(AreaHistory $areaHistory)
    {
        $this->updateVehicle($areaHistory->getVehicle()->addAreaHistory($areaHistory));
    }

    public function postUpdate(AreaHistory $areaHistory)
    {
        if ($areaHistory->getDeparted()) {
            $this->updateVehicle($areaHistory->getVehicle()->removeAreaHistory($areaHistory));
        }
    }

    public function postRemove(AreaHistory $areaHistory)
    {
        $this->updateVehicle($areaHistory->getVehicle()->removeAreaHistory($areaHistory));
    }

    /**
     * @param Vehicle $vehicle
     */
    protected function updateVehicle(Vehicle $vehicle)
    {
        try {
            $this->objectPersisterVehicle->replaceOne($vehicle);
        } catch (\Throwable $e) {

        }
    }

    /**
     * @param AreaHistory $areaHistory
     * @param LifecycleEventArgs $args
     * @return AreaHistory
     */
    public function postLoad(AreaHistory $areaHistory, LifecycleEventArgs $args)
    {
        $areaHistory->setEntityManager($this->em);

        return $areaHistory;
    }

    public function prePersist(AreaHistory $areaHistory, LifecycleEventArgs $args)
    {
        $areaHistory->setEntityManager($this->em);

        return $areaHistory;
    }
}
