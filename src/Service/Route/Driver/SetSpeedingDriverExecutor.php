<?php

namespace App\Service\Route\Driver;

use App\Entity\DriverHistory;
use App\Entity\Route;
use App\Entity\Speeding;
use App\Entity\User;
use App\Entity\Vehicle;
use Doctrine\ORM\EntityManager;

final class SetSpeedingDriverExecutor implements SetDriverExecutorInterface
{
    private $em;

    /**
     * @param EntityManager $em
     */
    public function __construct(EntityManager $em)
    {
        $this->em = $em;
    }

    /**
     * {@inheritDoc}
     */
    public function execute($driverHistoryId): void
    {
        // Since cascade operations aren't used then retrieve given driver directly.
        $driverHistory = $this->em->getRepository(DriverHistory::class)->find($driverHistoryId);

        if ($driverHistory) {
            $givenDriver = $this->em->getRepository(User::class)->find($driverHistory->getDriver());
            $iterator = $this->getSpeedingByVehicleAndStartedDateIterator(
                $driverHistory->getVehicle(),
                $driverHistory->getStartDate()
            );

            try {
                $i = 1;
                $batchSize = 20;
                foreach ($iterator as $row) {
                    /** @var Route $route */
                    $route = $row[0];
                    $route->setDriver($givenDriver);

                    if (($i % $batchSize) === 0) {
                        $this->em->flush();
                        $this->em->clear();
                        // After clearing forced set given driver into identity map.
                        $givenDriver = $this->em->getRepository(User::class)->find($driverHistory->getDriver());
                    }

                    ++$i;
                }

                $this->em->flush();
                $this->em->clear();
            } catch (\Exception $ex) {
                throw new \Exception($ex->getMessage());
            }
        }
    }

    /**
     * @param Vehicle $vehicle
     * @param \DateTime $startedAt
     *
     * @return iterable
     */
    private function getSpeedingByVehicleAndStartedDateIterator(Vehicle $vehicle, \DateTime $startedAt): iterable
    {
        return $this->em
            ->getRepository(Speeding::class)
            ->getSpeedingByVehicleAndStartedDateQb($vehicle, $startedAt)
            ->iterate();
    }
}
