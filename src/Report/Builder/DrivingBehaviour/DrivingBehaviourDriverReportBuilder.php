<?php

namespace App\Report\Builder\DrivingBehaviour;

use App\Entity\DriverHistory;
use App\Entity\DrivingBehavior;
use App\Entity\Route;
use App\Entity\Setting;
use App\Entity\Tracker\TrackerHistory;
use App\Entity\User;
use App\Entity\Vehicle;
use App\Report\ReportBuilder;
use App\Service\Report\ReportMapper;
use Pagerfanta\Adapter\CallbackAdapter;
use Pagerfanta\Pagerfanta;

class DrivingBehaviourDriverReportBuilder extends ReportBuilder
{
    public const REPORT_TYPE = ReportMapper::TYPE_DRIVING_BEHAVIOR_DRIVER;
    public const DEFAULT_REPORT_LIMIT = 10;

    public function getJson()
    {
        return $this->generateData();
    }

    public function getPdf()
    {
        return DrivingBehaviourReportHelper::prepareDriverExportData(
            $this->generateData(false), $this->params, $this->translator
        );
    }

    public function getCsv()
    {
        return DrivingBehaviourReportHelper::prepareDriverExportData(
            $this->generateData(false), $this->params, $this->translator
        );
    }

    public function generateData($paginated = true)
    {
        $params = DrivingBehaviourReportHelper::convertDatesToUTC($this->params);
        /** @var User[] $driversMap */
        $driversMap = DrivingBehaviourReportHelper::getMapBy(
            $this->userService->getDrivers(
                DrivingBehaviourReportHelper::prepareDriverSummaryElasticaParams($params),
                $this->user,
                false
            ),
            'id'
        );

        $params = DrivingBehaviourReportHelper::prepareDriversSummaryReportParams(
            array_merge(
                $params,
                [
                    'excSpeedMap' => DrivingBehaviourReportHelper::buildExcessiveSpeedMapForDrivers($driversMap),
                ]
            )
        );
//
//        $totalDistanceArray = $this->emSlave->getRepository(TrackerHistory::class)
//            ->getTotalOdometerByDriverArray(array_keys($driversMap), $params['startDate'], $params['endDate']);

        $totalDistanceArray = [];
        foreach ($driversMap as $key => $driver) {
            $totalDistanceArray[$key] = $this->em->getRepository(Route::class)
                ->getDistanceByDriver($driver, $params['startDate'], $params['endDate']);
        }
        foreach ($driversMap as $key => $driver) {
            $totalDistance = (int)($totalDistanceArray[$key] ?? 0);
            $drivingTotalTime = $this->emSlave->getRepository(TrackerHistory::class)
                ->getTotalDrivingTimeByDriver($driver, $params['startDate'], $params['endDate']);
            $totalAvgSpeed = $drivingTotalTime && $totalDistance
                ? (($totalDistance / 1000) / ($drivingTotalTime / 3600))
                : null;
            /** @var Setting $excessiveIdling */
            $excessiveIdling = $this->settingService->getExcessiveIdlingValueForTeam($driver->getTeam());

            $params['totalDistance'][$key] = $totalDistance;
            $params['drivingTotalTime'][$key] = $drivingTotalTime;
            $params['totalAvgSpeed'][$key] = $totalAvgSpeed;
            $params['excessiveIdling'][$key] = $excessiveIdling;
        }

        if ($paginated) {
            $adapter = $this->createAdapterForDriverSummary($driversMap, $params);
            $pagerfanta = new Pagerfanta($adapter);
            $pagerfanta->setMaxPerPage($params['limit'] ?? self::DEFAULT_REPORT_LIMIT);
            $pagerfanta->setCurrentPage($params['page'] ?? 1);

            return $pagerfanta;
        } else {
            $rawReport = [];

            foreach ($driversMap as $key => $driver) {
                $result = $this->em->getRepository(DrivingBehavior::class)
                    ->getSummaryDriverReport($params, $key);
                $result['totalDistance'] = $params['totalDistance'][$key];
                $result['drivingTotalTime'] = $params['drivingTotalTime'][$key];
                $result['totalAvgSpeed'] = $params['totalAvgSpeed'][$key];
                $result['excessiveIdling'] = $params['excessiveIdling'][$key];
                $rawReport[] = $result;
            }

            return $this->formatDriverRows($rawReport, $driversMap, $params);
        }
    }

    public function createAdapterForDriverSummary(
        $driversMap,
        &$params
    ): CallbackAdapter {
        return new CallbackAdapter(
            function () use ($driversMap) {
                return count($driversMap);
            },
            function ($offset, $limit) use ($params, $driversMap) {
                $params['offset'] = $offset;
                $params['limit'] = $limit;
                $data = [];

                foreach ($driversMap as $key => $driver) {
                    $result = $this->em->getRepository(DrivingBehavior::class)->getSummaryDriverReport(
                        $params,
                        $key
                    );
                    $result['totalDistance'] = $params['totalDistance'][$key];
                    $result['drivingTotalTime'] = $params['drivingTotalTime'][$key];
                    $result['totalAvgSpeed'] = $params['totalAvgSpeed'][$key];
                    $result['excessiveIdling'] = $params['excessiveIdling'][$key];
                    $data[] = $result;
                }

                $data = self::formatDriverRows($data, $driversMap, $params);
                $data = array_slice($data, $offset, $limit);

                return $data;
            }
        );
    }

    public function formatDriverRows(
        array $rawReport,
        array $driversMap,
        array $params
    ): array {
        $preparedReport = [];

        foreach ($rawReport as $item) {
            $preparedItem = DrivingBehaviourReportHelper::formatCommonRows($item);
            $preparedItem['driver'] = $driversMap[$item['driverid']]->toArray(['email', 'name', 'surname', 'fullName']);
            $preparedItem['name'] = $preparedItem['driver']['fullName'];
            $vehicleIds = $this->em->getRepository(DriverHistory::class)
                ->findVehiclesByDriverAndDate($item['driverid'], $params['startDate'], $params['endDate']);
            $preparedItem['vehicles'] = array_map(
                fn(Vehicle $vehicle) => $vehicle->toArray(['id', 'regNo', 'defaultLabel']),
                !empty($vehicleIds) ? $this->em->getRepository(Vehicle::class)->findBy(['id' => $vehicleIds]) : []
            );
            $preparedReport[] = $preparedItem;
        }

        if (isset($params['order'])) {
            $params['order'] = DrivingBehaviourReportHelper::mapOrder($params['order']);
            $preparedReport = DrivingBehaviourReportHelper::sortMultidimensionalArray(
                $preparedReport, ...array_shift($params['order'])
            );
        }

        return $preparedReport;
    }
}