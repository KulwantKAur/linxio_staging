<?php

namespace App\Report\Builder\Fuel;

use App\Report\Core\ResponseType\ArrayResponse;
use App\Report\ReportBuilder;
use App\Service\Report\ReportMapper;

class FuelRecordsByVehicleReportBuilder extends ReportBuilder
{
    public const REPORT_TYPE = ReportMapper::TYPE_FUEL_RECORDS_BY_VEHICLE;
    public const REPORT_TEMPLATE = 'reports/fuel-by-vehicle.html.twig';

    public function getJson()
    {
        return new ArrayResponse($this->fuelCardReportService->getFuelCardReport($this->params, $this->user));
    }

    public function getPdf()
    {
        return $this->fuelCardReportService->getFuelCardReportByVehicle($this->params, $this->user);
    }

    public function getCsv()
    {
        return $this->fuelCardReportService->getFuelCardReportExportData($this->params, $this->user);
    }
}