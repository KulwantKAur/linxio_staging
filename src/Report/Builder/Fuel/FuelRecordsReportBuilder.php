<?php

namespace App\Report\Builder\Fuel;

use App\Report\Core\ResponseType\ArrayResponse;
use App\Report\ReportBuilder;
use App\Service\Report\ReportMapper;

class FuelRecordsReportBuilder extends ReportBuilder
{
    public const REPORT_TYPE = ReportMapper::TYPE_FUEL_RECORDS;

    public function getJson()
    {
        return new ArrayResponse($this->fuelCardReportService->getFuelCardReport($this->params, $this->user));
    }

    public function getPdf()
    {
        return $this->fuelCardReportService->getFuelCardReportExportData($this->params, $this->user);
    }

    public function getCsv()
    {
        return $this->fuelCardReportService->getFuelCardReportExportData($this->params, $this->user);
    }
}