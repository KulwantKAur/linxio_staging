<?php

namespace App\Report\Builder\Fbt;

use App\Entity\BaseEntity;
use App\Entity\Route;
use App\Entity\Vehicle;
use App\Report\ReportBuilder;
use App\Service\Report\ReportMapper;
use App\Util\TranslateHelper;
use Carbon\Carbon;

class FbtBusinessVehicleReportBuilder extends ReportBuilder
{
    public const REPORT_TYPE = ReportMapper::TYPE_FBT_BUSINESS_VEHICLE;

    public function getJson()
    {
        return $this->generateData(true);
    }

    public function getPdf()
    {
        $data = $this->prepareExportData($this->generateData());
        $keys = array_keys($data[0] ?? []);
        $withoutTranslate = array_diff($keys, ['driver', 'installed', 'trial_finish', 'regno']);

        return TranslateHelper::translateEntityArrayForExport(
            $data, $this->translator, $keys, Route::class, null, $withoutTranslate
        );
    }

    public function getCsv()
    {
        $data = $this->prepareExportData($this->generateData());
        $keys = array_keys($data[0] ?? []);
        $withoutTranslate = array_diff($keys, ['driver', 'installed', 'trial_finish', 'regno']);

        return TranslateHelper::translateEntityArrayForExport(
            $data, $this->translator, $keys, Route::class, null, $withoutTranslate
        );
    }

    public function generateData($paginated = false)
    {
        $vehiclesData = $this->vehicleService->vehicleList($this->params, $this->user, $paginated, ['regNo']);
        $vehicles = $vehiclesData['data'] ?? $vehiclesData;

        $startDate = (new Carbon($this->params['startDate']))->setTimezone($this->user->getTimezone())->startOfWeek();
        $finishDate = (new Carbon($this->params['endDate']))->setTimezone($this->user->getTimezone())->endOfWeek();

        $data = [];

        if (is_object($vehicles[0] ?? null)) {
            $vehicleIds = array_map(fn(Vehicle $vehicle) => $vehicle->getId(), $vehicles);
        } else {
            $vehicleIds = array_map(fn(array $vehicle) => $vehicle['id'], $vehicles);
        }
        $tableName = 'fbt_vehicle_temp_' . time();
        $res = $this->em->getRepository(Route::class)->createTempRouteTableByDateRange(
            implode(', ', $vehicleIds), (clone $startDate)->subWeeks(12), $finishDate, $tableName
        );

        $firstRouteArray = $this->emSlave->getRepository(Route::class)->findFirstRouteByVehicle($vehicleIds);
        foreach ($vehicles as $vehicle) {
            $vehicle = is_object($vehicle) ? ['id' => $vehicle->getId(), 'regNo' => $vehicle->getRegNo()] : $vehicle;
            $vehicleId = $vehicle['id'];

            $firstRouteStartedAt = $firstRouteArray[array_search($vehicleId,
                array_column($firstRouteArray, 'vehicleId'))]['startedAt'] ?? null;
            $item = [];
            $item['data'] = [];
            $item['regno'] = $vehicle['regNo'];
            $item['driver'] = null;

            if ($firstRouteStartedAt) {
                $firstRouteStartedAt = new Carbon($firstRouteStartedAt);
                $item['installed'] = $firstRouteStartedAt->format('c');
                $weeks12Finish = (new Carbon($firstRouteStartedAt))->addWeeks(12);
                $item['trial_finish'] = $weeks12Finish->format('c');
                $finishDatePeriod = (clone $startDate)->addWeek();
                $driver = [];

                $vehicleRoutes = $this->em->getRepository(Route::class)
                    ->findRoutesFromTempTable($vehicleId, (clone $startDate)->subWeeks(12), $finishDate, $tableName);

                while ($finishDatePeriod <= $finishDate) {
                    $weekData = [];
                    $startDatePeriod = (clone $finishDatePeriod)->subWeeks(12);
                    $weekData['startDate'] = (clone $startDatePeriod)->setTimezone('UTC')->format('c');
                    $weekData['endDate'] = (clone $finishDatePeriod)->setTimezone('UTC')->format('c');

                    if (!$this->em->getRepository(Route::class)
                        ->findRoutesFromTempTable($vehicleId, (clone $finishDatePeriod)->subWeek(), $finishDatePeriod,
                            $tableName)) {
                        $weekData['percent'] = null;
                        $item['data'][] = $weekData;
                        $finishDatePeriod->addWeek();
                        continue;
                    }

                    if ($finishDatePeriod < $weeks12Finish) {
                        $r = null;
                    } else {
                        $r = array_filter($vehicleRoutes, function ($route) use ($finishDatePeriod, $startDatePeriod) {
                            return (new Carbon($route['started_at'])) < $finishDatePeriod && (new Carbon($route['finished_at'])) > $startDatePeriod;
                        });
                    }
                    $driverNames = array_map(
                        fn($route) => $route['driver_name'],
                        array_filter($r ?? [],
                            fn($route) => !empty($route['driver_name']))
                    );
                    $driver = array_unique(array_merge($driverNames, $driver));

                    if (is_null($r)) {
                        $weekData['percent'] = null;
                    } else {
                        $workRoutes = array_filter($r, fn($route) => $route['scope'] === Route::SCOPE_WORK);
                        $workDistance = array_sum(array_column($workRoutes, 'distance'));
                        $fullDistance = array_sum(array_column($r, 'distance'));
                        $weekData['percent'] = count($r) ? round($workDistance / ($fullDistance == 0 ? 1 : $fullDistance) * 100) : 0;
                    }

                    $item['data'][] = $weekData;
                    $finishDatePeriod->addWeek();
                }
                $item['driver'] = $driver ? implode(', ', $driver) : null;
            } else {
                $item['driver'] = null;
                $item['installed'] = null;
                $item['trial_finish'] = null;
                $finishDatePeriod = (clone $startDate)->addWeek();
                while ($finishDatePeriod <= $finishDate) {
                    $weekData = [];
                    $startDatePeriod = (clone $finishDatePeriod)->subWeeks(12);
                    $weekData['percent'] = null;

                    $weekData['startDate'] = $startDatePeriod->format('c');
                    $weekData['endDate'] = $finishDatePeriod->format('c');

                    $item['data'][] = $weekData;
                    $finishDatePeriod->addWeek();
                }
            }
            if (is_null($item['driver'])) {
                $item['regno'] .= '*';
            }

            $data[] = $item;
        }

        $this->em->getRepository(Route::class)->dropRouteTempTable($tableName);

        if ($paginated) {
            $pagination = $this->paginator->paginate($data, $this->page, ($this->limit == 0) ? 1 : $this->limit);
            $pagination->setItems($data);
            $pagination->setTotalItemCount($vehiclesData['total']);

            return $pagination;
        }

        return $data;
    }

    private function prepareExportData(array $data)
    {
        $timezone = $this->user->getTimezone();

        return array_map(function ($item) use ($timezone) {
            foreach ($item['data'] ?? [] as &$week) {
                $key = (new Carbon($week['endDate']))->format('d.m');
                $item[$key] = !is_null($week['percent']) ? $week['percent'] . '%' : null;
            }

            if ($item['installed'] ?? null) {
                $item['installed'] = (new Carbon($item['installed']))->setTimezone($timezone)
                    ->format(BaseEntity::EXPORT_DATE_FORMAT);
            }
            if ($item['trial_finish'] ?? null) {
                $item['trial_finish'] = (new Carbon($item['trial_finish']))->setTimezone($timezone)
                    ->format(BaseEntity::EXPORT_DATE_FORMAT);
            }

            unset($item['data']);
            return $item;
        }, $data);
    }
}