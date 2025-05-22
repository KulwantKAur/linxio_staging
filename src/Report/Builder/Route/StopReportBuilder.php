<?php

namespace App\Report\Builder\Route;

use App\Report\Builder\Route\Traits\StopTrait;
use App\Report\ReportBuilder;
use App\Service\Report\ReportMapper;

class StopReportBuilder extends ReportBuilder
{
    use StopTrait;

    public const REPORT_TYPE = ReportMapper::TYPE_STOPS;

    function getJson()
    {
        return $this->generateData();
    }

    function getPdf()
    {
        return RouteReportHelper::prepareExportData(
            $this->generateData(), $this->params, $this->user, $this->translator
        );
    }

    function getCsv()
    {
        return RouteReportHelper::prepareExportData(
            $this->generateData(), $this->params, $this->user, $this->translator
        );
    }
}