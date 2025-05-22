<?php

namespace App\Report\Builder\Summary;

use App\Entity\BaseEntity;
use App\Entity\Route;
use App\Entity\Tracker\TrackerHistory;
use App\Entity\User;
use App\Entity\Vehicle;
use App\Report\Builder\DrivingBehaviour\DrivingBehaviourReportHelper;
use App\Report\Core\DataType\DataWithTotal;
use App\Report\Core\DTO\TempRouteDTO;
use App\Report\Core\DTO\VehicleDaySummaryDTO;
use App\Report\ReportBuilder;
use App\Service\Report\ReportMapper;
use App\Service\Vehicle\VehicleService;
use App\Util\RequestFilterResolver\RequestFilterResolver;
use App\Util\StringHelper;
use Carbon\Carbon;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;

class VehicleDaySummaryReportBuilder extends ReportBuilder
{
    public const REPORT_TYPE = ReportMapper::TYPE_VEHICLE_DAY_SUMMARY;
    public const REPORT_TEMPLATE = 'reports/vehicle_day_summary.html.twig';
    public const FIELDS_FOR_TOTAL = [
        'private_distance',
        'work_distance',
        'distance',
        'stops',
        'parking_time',
        'driving_time',
        'engine_on_time'
    ];

    public function getJson()
    {
        $params = [
            PaginatorInterface::DEFAULT_SORT_FIELD_NAME => StringHelper::getSort($this->params, 'date'),
            PaginatorInterface::DEFAULT_SORT_DIRECTION => StringHelper::getOrder($this->params)
        ];
        $data = $this->generateData();
        $paginated = $this->paginator->paginate($data, $this->page, $this->limit, $params);

        return new DataWithTotal($paginated, $data[0]->total);
    }

    public function getPdf()
    {
        $this->params['fields'][] = 'defaultlabel';

        return SummaryReportHelper::prepareExportDataByVehicle(
            $this->generateData(), $this->params, $this->translator, false);
    }

    public function getCsv()
    {
        $this->params['fields'][] = 'defaultlabel';

        return SummaryReportHelper::prepareExportData(
            array_merge(...$this->generateData()), $this->params, $this->translator, false);
    }

    public function generateData()
    {
        return self::getRouteSummaryVehiclesData($this->params, $this->user, $this->emSlave, $this->vehicleService);
    }

    public static function getRouteSummaryVehiclesData(
        array $params,
        User $user,
        EntityManagerInterface $em,
        VehicleService $vehicleService,
        $vehicleList = false
    ) {
        $resolvedParams = RequestFilterResolver::resolve($params);
        $params = $resolvedParams + $params;
        $paramsDTO = new VehicleDaySummaryDTO($params);
        $elasticParams = SummaryReportHelper::getVehicleElasticSearchParams($params);
        $elasticParams['status'] = Vehicle::REPORT_STATUSES;
        $vehicles = $vehicleService->vehicleList($elasticParams, $user, false);
        $paramsDTO->vehicles = array_values(DrivingBehaviourReportHelper::buildExcessiveSpeedMap($vehicles));

        if (!$vehicles) {
            return [];
        }

        if ($vehicleList) {
            return $em->getRepository(Vehicle::class)->getVehiclesDaySummary($paramsDTO, $vehicleList);
        }

        $startDate = (new Carbon($paramsDTO->startDate))->setTimezone($user->getTimezone())->startOfDay();
        $endDate = (new Carbon($paramsDTO->endDate))->setTimezone($user->getTimezone())->endOfDay();

        $data = [];
        if (isset($params['vehicleIds']) && !is_array($params['vehicleIds'])) {
            return self::calculateVehicleData(clone $startDate, clone $endDate, $paramsDTO, $em, $vehicles[0]);
        } else {
            foreach ($vehicles as $vehicle) {
                $paramsDTO->vehicles = [$vehicle->getId()];
                $vehicleData = self::calculateVehicleData(clone $startDate, clone $endDate, $paramsDTO, $em, $vehicle);
                $data[] = $vehicleData;
            }
        }

        return $data;
    }

    private static function addRouteBeforeAfter(
        array $item,
        Vehicle $vehicle,
        string $startDate,
        string $endDate,
        EntityManagerInterface $em
    ): array {
        /** @var Route $routeBefore */
        $routeBefore = $em->getRepository(Route::class)->getPartialRouteByVehicle($vehicle, $startDate);
        /** @var Route $routeBefore */
        $routeAfter = $em->getRepository(Route::class)->getPartialRouteByVehicle($vehicle, $endDate);
        $thDataBefore = new TempRouteDTO($em->getRepository(TrackerHistory::class)
            ->getPartialRoute($vehicle, $startDate, $item['min_started_at']));
        $thDataAfter = new TempRouteDTO($em->getRepository(TrackerHistory::class)
            ->getPartialRoute($vehicle, $item['max_finished_at'], $endDate));
        $item['start_odometer'] = $thDataBefore->minOdometer
            ? ($thDataBefore->minOdometer + ($item['accuracy'] ?? 0))
            : ($item['start_odometer'] === 0 ? null : $item['start_odometer']);
        $item['end_odometer'] = $thDataAfter->maxOdometer
            ? ($thDataAfter->maxOdometer + ($item['accuracy'] ?? 0))
            : ($item['end_odometer'] === 0 ? null : $item['end_odometer']);
        $item['max_speed'] = max($thDataBefore->maxSpeed, $thDataAfter->maxSpeed, (int)$item['max_speed']);
        $item['distance'] += $thDataBefore->distance + $thDataAfter->distance;

        if ($routeBefore) {
            $item = self::handleThDataByRoute($item, $thDataBefore, $routeBefore);
            $item['stops'] += (int)$routeBefore->isStopType();
        }
        if ($routeAfter) {
            $item = self::handleThDataByRoute($item, $thDataAfter, $routeAfter);
            $item['stops'] += (int)$routeAfter->isStopType();
        }


        return $item;
    }

    private static function handleThDataByRoute(array $item, TempRouteDTO $thData, Route $route): array
    {
        if ($route->isStopType()) {
            $item['parking_time'] += $thData->duration;
        } else {
            $item['driving_time'] += $thData->duration;
        }
        $item['private_distance'] += $route->getType() == Route::SCOPE_PRIVATE ? $thData->distance : 0;
        $item['work_distance'] += $route->getType() == Route::SCOPE_WORK ? $thData->distance : 0;

        return $item;
    }

    private static function calculateTotalData(array $data): array
    {
        $total = [];

        foreach ($data as $day) {
            foreach (self::FIELDS_FOR_TOTAL as $fieldName) {
                $total['total_' . $fieldName] = $total['total_' . $fieldName] ?? 0;
                $total['total_' . $fieldName] += $day->$fieldName ?? 0;
            }
        }

        return $total;
    }

    private static function calculateVehicleData(
        \DateTime $startDate,
        \DateTime $endDate,
        VehicleDaySummaryDTO $paramsDTO,
        EntityManagerInterface $em,
        Vehicle $vehicle
    ): array {
        $data = [];
        while ($startDate < $endDate) {
            $paramsDTO->startDate = (clone $startDate)->setTimezone('UTC')->format('c');
            $paramsDTO->endDate = (clone $startDate)->addDay()->setTimezone('UTC')->format('c');
            $itemData = $em->getRepository(Vehicle::class)->getVehiclesDaySummary($paramsDTO)->execute()->fetchAll()[0] ?? [];
            $itemData = self::addRouteBeforeAfter($itemData, $vehicle, $paramsDTO->startDate, $paramsDTO->endDate, $em);
            $date = (clone $startDate)->format('Y-m-d');
            $data[] = (object)array_merge(['date' => $date], $itemData);
            $startDate->addDay();
        }

        if (isset($data[0])) {
            $data[0]->total = self::calculateTotalData($data);
        }

        return $data;
    }
}