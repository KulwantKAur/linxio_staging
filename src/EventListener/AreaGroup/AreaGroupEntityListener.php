<?php

namespace App\EventListener\AreaGroup;

use App\Entity\AreaGroup;
use App\Entity\AreaHistory;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Event\LifecycleEventArgs;
use FOS\ElasticaBundle\Persister\ObjectPersister;
use Psr\Container\ContainerInterface;

class AreaGroupEntityListener
{
    private $objectPersisterVehicle;
    private $container;

    /**
     * AreaGroupEntityListener constructor.
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container, ObjectPersister $objectPersister)
    {
        $this->container = $container;
        $this->objectPersisterVehicle = $objectPersister;
    }

    public function postPersist(AreaGroup $areaGroup)
    {
        $this->updateVehicle($areaGroup);
    }

    public function postUpdate(AreaGroup $areaGroup)
    {
        $this->updateVehicle($areaGroup);
    }

    /**
     * @param AreaGroup $areaGroup
     */
    protected function updateVehicle(AreaGroup $areaGroup)
    {
        $deletedAreas = $areaGroup->deletedAreas ?? [];
        $em = $this->container->get('doctrine.orm.default_entity_manager');
        $areas = $areaGroup->getAreasEntities()->toArray();

        $vehicles = new ArrayCollection();
        foreach ($deletedAreas as $area) {
            $areaHistories = $em->getRepository(AreaHistory::class)->findBy(['area' => $area, 'departed' => null]);
            foreach ($areaHistories as $areaHistory) {
                $vehicle = $areaHistory->getVehicle();
                if (!$vehicles->contains($vehicle)) {
                    $vehicles->add($vehicle);
                }
            }
        }

        if ($vehicles->count()) {
            $this->objectPersisterVehicle->replaceMany($vehicles->toArray());
        }

        $areaHistories = $em->getRepository(AreaHistory::class)->findBy(['area' => $areas, 'departed' => null]);
        $vehicles = new ArrayCollection();

        foreach ($areaHistories as $areaHistory) {
            $vehicle = $areaHistory->getVehicle();
            if (!$vehicles->contains($vehicle)) {
                $vehicles->add($vehicle);
            }
        }
        if ($vehicles->count()) {
            $this->objectPersisterVehicle->replaceMany($vehicles->toArray());
        }
    }

    public function postLoad(AreaGroup $areaGroup, LifecycleEventArgs $args)
    {
    }
}