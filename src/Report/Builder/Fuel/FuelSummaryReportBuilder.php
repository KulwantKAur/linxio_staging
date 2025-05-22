<?php

namespace App\Report\Builder\Fuel;

use App\Entity\FuelCard\FuelCard;
use App\Report\ReportBuilder;
use App\Service\Report\ReportMapper;

class FuelSummaryReportBuilder extends ReportBuilder
{
    public const REPORT_TYPE = ReportMapper::TYPE_FUEL_SUMMARY;

    public function getJson()
    {
        return $this->generateData();
    }

    public function getPdf()
    {
        return FuelReportHelper::prepareExportSummaryData($this->generateData(), $this->params, $this->translator);
    }

    public function getCsv()
    {
        return FuelReportHelper::prepareExportSummaryData($this->generateData(), $this->params, $this->translator);
    }

    private function generateData()
    {
        return $this->emSlave->getRepository(FuelCard::class)
            ->getQueryDataForFuelSummary(FuelReportHelper::getPrepareFields($this->params, $this->user));
    }
}
