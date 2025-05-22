<?php

namespace App\Report\Builder\Route;

use App\Report\Builder\Route\Traits\RouteTrait;
use App\Report\ReportBuilder;
use App\Service\Report\ReportMapper;

class RouteReportBuilder extends ReportBuilder
{
    use RouteTrait;

    public const REPORT_TYPE = ReportMapper::TYPE_ROUTES;

    public function getJson()
    {
        return $this->generateData();
    }

    public function getPdf()
    {
        return RouteReportHelper::prepareExportData(
            $this->generateData(),
            $this->params,
            $this->user,
            $this->translator
        );
    }

    public function getCsv()
    {
        return RouteReportHelper::prepareExportData(
            $this->generateData(),
            $this->params,
            $this->user,
            $this->translator
        );
    }
}
