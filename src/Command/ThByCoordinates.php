<?php

namespace App\Command;

use App\Entity\Device;
use App\Entity\Route;
use App\Entity\Tracker\TrackerHistory;
use App\Entity\Vehicle;
use App\Entity\VehicleOdometer;
use App\Util\DateHelper;
use Doctrine\ORM\EntityManager;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'th-by-coordinate')]
class ThByCoordinates extends Command
{
    private EntityManager $em;

    protected function configure(): void
    {
        $this->setDescription('Recalcute route by coordinates');
        $this->addOption('vehicleId', null, InputOption::VALUE_OPTIONAL);
        $this->addOption('interval', null, InputOption::VALUE_OPTIONAL);
        $this->addOption('saveTh', null, InputOption::VALUE_OPTIONAL);
        $this->addOption('saveRoute', null, InputOption::VALUE_OPTIONAL);
        $this->addOption('correction', null, InputOption::VALUE_OPTIONAL);
        $this->addOption('result', null, InputOption::VALUE_OPTIONAL);
    }

    public function __construct(
        EntityManager $em
    ) {
        $this->em = $em;

        parent::__construct();
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int|void|null
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $vehicleId = $input->getOption('vehicleId');
        $saveTh = $input->getOption('saveTh');
        $saveRoute = $input->getOption('saveRoute');
        $correction = $input->getOption('correction');
        $showResult = $input->getOption('result');
        $vehicle = $this->em->getRepository(Vehicle::class)->find($vehicleId);

        $vehicleOdometers = $this->em->getRepository(VehicleOdometer::class)->queryAllByVehicle($vehicle)->getResult();
        if (count($vehicleOdometers) === 0) {
            $lastTh = $this->em->getRepository(TrackerHistory::class)->getLastRecordByVehicle($vehicle);
        }
        $loopCount = count($vehicleOdometers) ?: ($lastTh ? 1 : 0);
        $diffPercent = 1;
        $odometers = $this->formatLoopItem(count($vehicleOdometers) ? $vehicleOdometers : ($lastTh ? [$lastTh] : []));
        for ($i = 0; $i < $loopCount; $i++) {
            /** @var VehicleOdometer $odometer */
            $odometer = $odometers[$i];

            $accuracy = $correction ? $odometer['accuracy'] : 0;

            $nextOdometer = $odometers[$i + 1] ?? null;
            if ($nextOdometer) {
                $odometerDiff = $odometer['odometer'] - $nextOdometer['odometer'];
                $diffPercent = null;
            }

            $thQuery = $this->em->getRepository(TrackerHistory::class)
                ->getThIterable($vehicle, $nextOdometer['ts'] ?? null, $odometer['ts']);
            $rQuery = $this->em->getRepository(Route::class)
                ->getRouteIterable($vehicle, $nextOdometer['ts'] ?? null, $odometer['ts']);

            $distanceTh = [];
            $prevTh = null;
            $distanceByCoordinates = 0;
            /** @var TrackerHistory $th */
            foreach ($thQuery->toIterable() as $th) {

                if (!$prevTh) {
                    $prevTh = $th;
                    $prevTh['odometer'] = $odometer['odometer'];
                    continue;
                }

                $distanceTh[$th['id']] = [
                    'distance' => $this->haversineGreatCircleDistance(
                        $prevTh['lat'], $prevTh['lng'], $th['lat'], $th['lng']
                    ),
                    'prevThId' => $prevTh['id'],
                    'thId' => $th['id'],
//                    'prevThOdometer' => $prevTh['odometer']
                ];
                $distanceByCoordinates += $distanceTh[$th['id']]['distance'];

                $thOdometer = $prevTh['odometer'] - $distanceTh[$th['id']]['distance'];
                $th['odometer'] = $thOdometer;
//                $distanceTh[$th->getId()]['thOdometer'] = $th['odometer'];

                $prevTh = $th;
//                $output->writeln('distance - ' . $distanceTh[$th->getId()]['distance']);
//                $output->writeln($th->getId());
            }
            $diffPercent = is_null($diffPercent) ? $odometerDiff / $distanceByCoordinates : $diffPercent;
            $output->writeln('coefficient ' . $diffPercent);

            $prevTh = null;
            foreach ($thQuery->toIterable() as $th) {
                if (!$prevTh) {
                    $prevTh = $th;
                    $prevTh['odometer'] = $odometer['odometer'] - $accuracy;
                    continue;
                }
                $distanceTh[$th['id']] = [
                    'distance' => $distanceTh[$th['id']]['distance'] * $diffPercent,
                    'distance_by_coord' => $distanceTh[$th['id']]['distance'],
                    'prevThId' => $prevTh['id'],
                    'thId' => $th['id'],
                    'prevThOdometer' => $prevTh['odometer']
                ];

                $thOdometer = $prevTh['odometer'] - $distanceTh[$th['id']]['distance'];
                $th['odometer'] = $thOdometer;

                if ($saveTh) {
                    $this->em->getRepository(TrackerHistory::class)
                        ->updateTrackerHistoryOdometerById($th['id'], $thOdometer);
                }

                $distanceTh[$th['id']]['thOdometer'] = $th['odometer'];
//
                $prevTh = $th;
            }

            $result = [];
            $prevRoute = null;
            /** @var Route $r */
            foreach ($rQuery->toIterable() as $r) {
                $item = [];
                $routeDistance = 0;
                $routeDistanceByCoord = 0;
                $setFinishOdometer = false;

//                //border route
//                if($r->getFinishedAt() > $odometer['ts']){
//
//                }
                $item['route_id'] = $r->getId();
//                $item['old_distance'] = $r->getDistance();
//                $item['old_start_odometer'] = $r->getStartOdometer();
//                $item['old_finish_odometer'] = $r->getFinishOdometer();
                foreach ($distanceTh as $th) {
                    if ($th['prevThId'] <= $r->getPointFinish()->getId() && $th['thId'] >= $r->getPointStart()->getId()) {
                        if (!$setFinishOdometer) {
                            $r->setFinishOdometer($th['prevThOdometer']);
                            $setFinishOdometer = true;
                        }
                        $routeDistance += $th['distance'];
                        $routeDistanceByCoord += $th['distance_by_coord'];
                        $r->setStartOdometer($th['thOdometer']);
                    }
                }
                if (!$setFinishOdometer && $prevRoute) {
                    $r->setFinishOdometer($prevRoute->getStartOdometer());
                    $r->setStartOdometer($prevRoute->getStartOdometer());
                    $r->setDistance(0);
                }
                //TODO plus another route part
                $item['started_at'] = $r->getStartedAt()->getTimestamp();
                $item['new_start_odometer'] = round($r->getStartOdometer() / 1000, 1);
                $item['new_finish_odometer'] = round($r->getFinishOdometer() / 1000, 1);
                $item['old_distance'] = round($r->getDistance() / 1000, 1);
//                $output->writeln('route old distance - ' . round($r->getDistance() / 1000, 1));
                $r->setDistance($routeDistance);

                $item['new_distance'] = round($r->getDistance() / 1000, 1);
                $item['distance_by_coord'] = round($routeDistanceByCoord / 1000, 1);
//                $output->writeln('route new distance - ' .  $item['new_distance']);
                $result[] = $item;
                $prevRoute = $r;
            }
            if ($saveRoute) {
                $this->em->flush();
            }
            if ($showResult) {
                $output->writeln(json_encode($result));
            }
        }

        return 0;
    }

    /**
     * Calculates the great-circle distance between two points, with
     * the Haversine formula.
     * @param float $latitudeFrom Latitude of start point in [deg decimal]
     * @param float $longitudeFrom Longitude of start point in [deg decimal]
     * @param float $latitudeTo Latitude of target point in [deg decimal]
     * @param float $longitudeTo Longitude of target point in [deg decimal]
     * @param float $earthRadius Mean earth radius in [m]
     * @return float Distance between points in [m] (same as earthRadius)
     */
    function haversineGreatCircleDistance(
        $latitudeFrom,
        $longitudeFrom,
        $latitudeTo,
        $longitudeTo,
        $earthRadius = 6371000
    ) {
        // convert from degrees to radians
        $latFrom = deg2rad($latitudeFrom);
        $lonFrom = deg2rad($longitudeFrom);
        $latTo = deg2rad($latitudeTo);
        $lonTo = deg2rad($longitudeTo);

        $lonDelta = $lonTo - $lonFrom;
        $a = pow(cos($latTo) * sin($lonDelta), 2) +
            pow(cos($latFrom) * sin($latTo) - sin($latFrom) * cos($latTo) * cos($lonDelta), 2);
        $b = sin($latFrom) * sin($latTo) + cos($latFrom) * cos($latTo) * cos($lonDelta);

        $angle = atan2(sqrt($a), $b);

        return round($angle * $earthRadius);
    }

    private function formatLoopItem(array $items): array
    {
        return array_map(function ($item) {
            if ($item instanceof VehicleOdometer) {
                return [
                    'odometer' => $item->getOdometer(),
                    'ts' => $item->getOccurredAt(),
                    'accuracy' => $item->getAccuracy()
                ];
            } elseif ($item instanceof TrackerHistory) {
                return ['odometer' => $item->getOdometer(), 'ts' => $item->getTs(), 'accuracy' => 0];
            }
            return [];
        }, $items);
    }
}