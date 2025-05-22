<?php

namespace App\Repository;

use App\Entity\FleetioVehicle;
use App\Entity\Vehicle;
use Doctrine\ORM\EntityRepository;

class FleetioVehicleRepository extends EntityRepository
{
    public function getFleetioVehicleIdByVehicle(Vehicle $vehicle)
    {
        $result = $this->getEntityManager()->createQueryBuilder()
            ->select('fv.fleetioVehicleId')
            ->from(FleetioVehicle::class, 'fv')
            ->andWhere('fv.vehicle = :vehicle')
            ->setParameter('vehicle', $vehicle)
            ->getQuery()
            ->setMaxResults(1)
            ->getOneOrNullResult();

        return $result ? $result['fleetioVehicleId'] : null;
    }
}
