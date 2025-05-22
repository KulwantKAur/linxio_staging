<?php

namespace App\Report\Builder\Fbt;

use App\Entity\Route;
use App\Entity\Team;
use App\Entity\User;
use App\Report\Builder\Route\RouteReportHelper;
use App\Report\Core\DTO\FbtVehicleReportDTO;
use App\Report\ReportBuilder;
use App\Service\Report\ReportMapper;

class FbtVehicleReportBuilder extends ReportBuilder
{
    public const REPORT_TYPE = ReportMapper::TYPE_FBT_VEHICLE;
    public const REPORT_TEMPLATE = 'reports/fbt-vehicle.html.twig';
    private const TOTAL_FIELDS = [
        'all_total_distance',
        'all_total_duration',
        'all_total_work_distance',
        'all_total_work_duration',
        'all_total_work_percentage',
        'all_total_private_distance',
        'all_total_private_duration',
        'all_total_private_percentage',
        'all_total_unclassified_distance',
        'all_total_unclassified_duration',
        'all_total_unclassified_percentage',
    ];

    public function getJson()
    {
        return $this->generateData();
    }

    public function getPdf()
    {
        return RouteReportHelper::prepareExportData(
            $this->generateData(), $this->params, $this->user, $this->translator, self::TOTAL_FIELDS
        );
    }

    public function getCsv()
    {
        return RouteReportHelper::prepareExportData(
            $this->generateData(), $this->params, $this->user, $this->translator
        );
    }

    public function generateData()
    {
        $this->params['teamId'] = [$this->user->getTeamId()];
        if ($this->params['driver_name'] ?? null) {
            $usersParams = array_merge($this->params,
                ['teamType' => Team::TEAM_CLIENT, 'fullName' => $this->params['driver_name']]
            );
            unset($usersParams['vehicleIds']);
            $users = $this->userService->usersList($usersParams, false);
            $this->params['driverIds'] = array_map(fn(User $user) => $user->getId(), $users);
        }

        return $this->emSlave->getRepository(Route::class)->getRoutesVehicleFbt(new FbtVehicleReportDTO($this->params));
    }
}