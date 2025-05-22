<?php

namespace App\Report\Builder\Summary;

use App\Entity\Route;
use App\Report\Builder\Route\RouteReportHelper;
use App\Report\ReportBuilder;
use App\Service\Report\ReportMapper;
use App\Service\User\UserServiceHelper;
use App\Service\Vehicle\VehicleServiceHelper;

class DriverSummaryReportBuilder extends ReportBuilder
{
    public const REPORT_TYPE = ReportMapper::TYPE_DRIVER_SUMMARY;
    public const REPORT_TEMPLATE = 'reports/driver-summary-by-vehicle.html.twig';

    public function getJson()
    {
        return $this->generateData();
    }

    public function getPdf()
    {
        return SummaryReportHelper::getDriverSummaryReportByVehicle(
            $this->params, $this->user, $this->generateData(), $this->translator, $this->emSlave
        );
    }

    public function getCsv()
    {
        return SummaryReportHelper::prepareDriverSummaryExportData(
            $this->generateData(), $this->params, $this->user, $this->translator
        );
    }

    public function generateData()
    {
        $params = UserServiceHelper::handleTeamParams($this->params, $this->user);
        $params['sort'] = $params['sort'] ?? 'regno';
        $params = VehicleServiceHelper::handleDriverVehicleParams($params, $this->em, $this->user, false);

        return $this->emSlave->getRepository(Route::class)->getDriverSummary(RouteReportHelper::prepareFields($params));
    }
}