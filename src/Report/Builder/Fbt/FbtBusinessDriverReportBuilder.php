<?php

namespace App\Report\Builder\Fbt;

use App\Entity\BaseEntity;
use App\Entity\DriverHistory;
use App\Entity\Role;
use App\Entity\Route;
use App\Entity\Team;
use App\Entity\User;
use App\Report\ReportBuilder;
use App\Service\Report\ReportMapper;
use App\Util\TranslateHelper;
use Carbon\Carbon;

class FbtBusinessDriverReportBuilder extends ReportBuilder
{
    public const REPORT_TYPE = ReportMapper::TYPE_FBT_BUSINESS_DRIVER;

    public function getJson()
    {
        return $this->generateData();
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

    public function generateData()
    {
        // @todo do smth for `isDualAccount`?
        $usersParams = array_merge($this->params,
            ['teamType' => Team::TEAM_CLIENT, 'role' => Role::ROLE_CLIENT_DRIVER, 'teamId' => $this->user->getTeamId()]
        );
        $users = $this->userService->usersList($usersParams, false);
        $this->params['driverIds'] = array_map(fn(User $user) => $user->getId(), $users);

        $startDate = (new Carbon($this->params['startDate']))->setTimezone($this->user->getTimezone())->startOfWeek();
        $finishDate = (new Carbon($this->params['endDate']))->setTimezone($this->user->getTimezone())->endOfWeek();

        $data = [];
        $firstRoutes = $this->emSlave->getRepository(Route::class)->findUsersFirstRoute($this->params['driverIds'] ?? []);
        $fRoutes = [];
        foreach ($firstRoutes as $firstRoute) {
            $fRoutes[$firstRoute['driver_id']] = $firstRoute;
        }

        if (isset($this->params['limit']) && isset($this->params['page'])) {
            $offset = $this->params['limit'] * $this->params['page'] - $this->params['limit'];
            $driverIdForData = array_slice($this->params['driverIds'], $offset, $this->params['limit']);
        } else {
            $driverIdForData = $this->params['driverIds'];
        }

        foreach ($users as $user) {
            $item = [];
            $item['data'] = [];
            $item['driver'] = $user->getFullName();
            $item['regno'] = null;
            $firstRoute = $fRoutes[$user->getId()] ?? null;

            if ($firstRoute && in_array($user->getId(), $driverIdForData)) {
                $item['installed'] = Carbon::parse($firstRoute['started_at'])->format('c');
                $weeks12Finish = Carbon::parse($firstRoute['started_at'])->addWeeks(12);
                $item['trial_finish'] = $weeks12Finish->format('c');
                $finishDatePeriod = (clone $startDate)->addWeek();
                $regno = [];

                while ($finishDatePeriod <= $finishDate) {
                    $weekData = [];
                    $startDatePeriod = (clone $finishDatePeriod)->subWeeks(12);
                    $weekData['startDate'] = (clone $startDatePeriod)->setTimezone('UTC')->format('c');
                    $weekData['endDate'] = (clone $finishDatePeriod)->setTimezone('UTC')->format('c');

                    if (!$this->emSlave->getRepository(Route::class)
                        ->getRoutesByDriverAndRange($user, (clone $finishDatePeriod)->subWeek(), $finishDatePeriod)) {
                        $weekData['percent'] = null;
                        $item['data'][] = $weekData;
                        $finishDatePeriod->addWeek();
                        continue;
                    }

                    if ($finishDatePeriod < $weeks12Finish) {
                        $r = null;
                    }
//                    elseif ($startDatePeriod < $weeks12Finish) {
//                        $r = $this->emSlave->getRepository(Route::class)
//                            ->getRoutesByDriverAndRange($user, $weeks12Finish, $finishDatePeriod);
//                    }
                    else {
                        $r = $this->emSlave->getRepository(Route::class)
                            ->getRoutesByDriverAndRange($user, $startDatePeriod, $finishDatePeriod);
                    }
                    $regno = array_unique(array_merge(array_map(fn($route) => $route['regno'], $r ?? []), $regno));

                    if (is_null($r)) {
                        $weekData['percent'] = null;
                    } else {
                        $workRoutes = array_filter($r, fn($route) => $route['scope'] === Route::SCOPE_WORK);
                        $workDistance = array_sum(array_column($workRoutes, 'distance'));
                        $fullDistance = array_sum(array_column($r, 'distance'));
                        $weekData['percent'] = count($r) ? round($workDistance / ($fullDistance == 0 ? 1 : $fullDistance) * 100) : 0;
                    }

                    $weekData['startDate'] = $startDatePeriod->format('c');
                    $weekData['endDate'] = $finishDatePeriod->format('c');

                    $item['data'][] = $weekData;
                    $finishDatePeriod->addWeek();
                }
                $item['regno'] = $regno ? implode(', ', $regno) : null;
            } else {
                $item['regno'] = null;
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
            $data[] = $item;
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