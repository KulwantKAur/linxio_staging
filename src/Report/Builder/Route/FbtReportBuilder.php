<?php

namespace App\Report\Builder\Route;

use App\Entity\Route;
use App\Report\Core\DTO\FbtReportDTO;
use App\Report\ReportBuilder;
use App\Service\Report\ReportMapper;
use App\Service\User\UserServiceHelper;

class FbtReportBuilder extends ReportBuilder
{
    public const REPORT_TYPE = ReportMapper::TYPE_FBT;

    public function getJson()
    {
        return $this->generateData();
    }

    public function getPdf()
    {
        return RouteReportHelper::prepareExportData(
            $this->generateData(), $this->params, $this->user, $this->translator
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
        $params = UserServiceHelper::handleTeamParams($this->params, $this->user);

        return $this->emSlave->getRepository(Route::class)->getRoutesFbt(new FbtReportDTO($params));
    }
}