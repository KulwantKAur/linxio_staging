<?php

namespace App\Report\Builder\DrivingBehaviour;

use App\Entity\BaseEntity;
use App\Entity\DriverHistory;
use App\Entity\DrivingBehavior;
use App\Entity\User;
use App\Entity\Vehicle;
use App\Report\ReportBuilder;
use App\Service\Report\ReportMapper;
use Doctrine\ORM\EntityManagerInterface;
use Pagerfanta\Adapter\CallbackAdapter;
use Pagerfanta\Pagerfanta;

class DrivingBehaviourVehicleReportBuilder extends ReportBuilder
{
    public const REPORT_TYPE = ReportMapper::TYPE_DRIVING_BEHAVIOR_VEHICLE;
    public const DEFAULT_REPORT_LIMIT = 10;

    public function getJson()
    {
        return $this->generateData();
    }

    public function getPdf()
    {
        return DrivingBehaviourReportHelper::prepareVehicleExportData(
            $this->generateData(false), $this->params, $this->translator
        );
    }

    public function getCsv()
    {
        return DrivingBehaviourReportHelper::prepareVehicleExportData(
            $this->generateData(false), $this->params, $this->translator
        );
    }

    public function generateData(bool $paginated = true)
    {
        $params = DrivingBehaviourReportHelper::convertDatesToUTC($this->params);
        $elasticaParams = DrivingBehaviourReportHelper::prepareVehicleSummaryElasticaParams($params);
        $elasticaParams = Vehicle::handleStatusParams($elasticaParams);;
        /** @var Vehicle[] $vehiclesMap */
        $vehiclesMap = DrivingBehaviourReportHelper::getMapBy(
            $this->vehicleService->vehicleList(
                $elasticaParams,
                $this->user,
                false
            ),
            'id'
        );

        $params = DrivingBehaviourReportHelper::prepareVehiclesSummaryReportParams(
            array_merge(
                $params,
                [
                    'excSpeedMap' => DrivingBehaviourReportHelper::buildExcessiveSpeedMap($vehiclesMap),
                ]
            )
        );

        $totalDistanceArray = $this->emSlave->getRepository(Vehicle::class)
            ->getTotalOdometerArray(array_keys($vehiclesMap), $params['startDate'], $params['endDate']);
        $vehiclesDrivingTotalTime = $drivingTotalTime = $this->emSlave->getRepository(Vehicle::class)
            ->getTotalDrivingTimeForArray($vehiclesMap, $params['startDate'], $params['endDate']);

        foreach ($vehiclesMap as $key => $vehicle) {
            $totalDistance = (int)($totalDistanceArray[$key] ?? 0);
            $drivingTotalTime = $vehiclesDrivingTotalTime[$vehicle->getId()] ?? 0;

            $totalAvgSpeed = $drivingTotalTime && $totalDistance ? (($totalDistance / 1000) / ($drivingTotalTime / 3600)) : null;
            $excessiveIdling = $this->settingService->getExcessiveIdlingValue($vehicle);

            $params['totalDistance'][$key] = $totalDistance;
            $params['drivingTotalTime'][$key] = $drivingTotalTime;
            $params['totalAvgSpeed'][$key] = $totalAvgSpeed;
            $params['excessiveIdling'][$key] = $excessiveIdling;
        }

        if ($paginated) {
            $adapter = $this->createAdapterForVehicleSummary(
                $vehiclesMap, $params, $this->emSlave
            );
            $pagerfanta = new Pagerfanta($adapter);
            $pagerfanta->setMaxPerPage($params['limit'] ?? self::DEFAULT_REPORT_LIMIT);
            $pagerfanta->setCurrentPage($params['page'] ?? 1);

            return $pagerfanta;
        } else {
            $rawReport = [];

            foreach ($vehiclesMap as $key => $vehicle) {
                $result = $this->emSlave->getRepository(DrivingBehavior::class)->getSummaryVehicleReport($params, $key);
                $result['totalDistance'] = $params['totalDistance'][$key];
                $result['drivingTotalTime'] = $params['drivingTotalTime'][$key];
                $result['totalAvgSpeed'] = $params['totalAvgSpeed'][$key];
                $result['excessiveIdling'] = $params['excessiveIdling'][$key];
                $rawReport[] = $result;
            }

            return $this->formatVehicleRows($rawReport, $params);
        }
    }

    public function createAdapterForVehicleSummary(
        $vehiclesMap,
        &$params,
        EntityManagerInterface $em
    ): CallbackAdapter {
        return new CallbackAdapter(
            fn() => count($vehiclesMap),
            function ($offset, $limit) use ($params, $vehiclesMap, $em) {
                $params['offset'] = $offset;
                $params['limit'] = $limit;
                $data = [];

                foreach ($vehiclesMap as $key => $vehicle) {
                    $result = $this->emSlave->getRepository(DrivingBehavior::class)
                        ->getSummaryVehicleReport($params, $key);
                    $result['totalDistance'] = $params['totalDistance'][$key];
                    $result['drivingTotalTime'] = $params['drivingTotalTime'][$key];
                    $result['totalAvgSpeed'] = $params['totalAvgSpeed'][$key];
                    $result['excessiveIdling'] = $params['excessiveIdling'][$key];
                    $data[] = $result;
                }

                $data = self::formatVehicleRows($data, $params);
                $data = array_slice($data, $offset, $limit);

                return $data;
            }
        );
    }

    public function formatVehicleRows(
        array $rawReport,
        array $params
    ): array {
        $preparedReport = [];
        $vehicleIds = array_column($rawReport, 'vehicleid');
        $vehiclesData = $this->em->getRepository(Vehicle::class)->getVehicleForDrivingBehaviour($vehicleIds);

        foreach ($rawReport as $item) {
            $preparedItem = DrivingBehaviourReportHelper::formatCommonRows($item);
            $preparedItem['vehicle'] = $vehiclesData[$item['vehicleid']]
                ->toArray(['id', 'regNo', 'defaultLabel', 'depot', 'groups', 'groupsList']);
            $preparedItem['regNo'] = $preparedItem['vehicle']['regNo'];
            $preparedItem['groups'] = $preparedItem['vehicle']['groupsList'];
            $preparedItem['depot'] = $preparedItem['vehicle']['depot']['name'] ?? null;

            $driverIds = $this->em->getRepository(DriverHistory::class)
                ->findDriversByVehicleAndDate($item['vehicleid'], $params['startDate'], $params['endDate']);
            $preparedItem['drivers'] = array_map(
                fn(User $user) => $user->toArray(['id', 'name', 'surname', 'fullName']),
                !empty($driverIds) ? $this->em->getRepository(User::class)->findBy(['id' => $driverIds]) : []
            );
            $preparedReport[] = $preparedItem;
        }

        if (isset($params['order'])) {
            $params['order'] = DrivingBehaviourReportHelper::mapOrder($params['order']);
            $preparedReport = DrivingBehaviourReportHelper::sortMultidimensionalArray(
                $preparedReport, ...array_shift($params['order']));
        }

        return $preparedReport;
    }
}