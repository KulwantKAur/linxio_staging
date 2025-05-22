<?php

namespace App\Report\Builder\Area;

use App\Entity\AreaHistory;
use App\Report\ReportBuilder;
use App\Service\Report\ReportMapper;
use App\Service\User\UserServiceHelper;
use App\Util\RequestFilterResolver\RequestFilterResolver;

class AreaSummaryReportBuilder extends ReportBuilder
{
    public const REPORT_TYPE = ReportMapper::TYPE_AREA_SUMMARY;

    public function getJson()
    {
        return $this->generateData();
    }

    public function getPdf()
    {
        return AreaReportHelper::prepareExportData(
            $this->generateData(), $this->params, $this->user, $this->translator
        );
    }

    public function getCsv()
    {
        return AreaReportHelper::prepareExportData(
            $this->generateData(), $this->params, $this->user, $this->translator
        );
    }

    private function generateData()
    {
        $resolvedParams = RequestFilterResolver::resolve($this->params);
        $params = $resolvedParams + $this->params;

        $vehicles = $this->vehicleService->vehicleList([], $this->user, false);

        $params = UserServiceHelper::handleTeamParams($params, $this->user);

        $params['vehicles'] = $vehicles;

        return $this->emSlave->getRepository(AreaHistory::class)->getAreasSummary($params, $this->user);
    }
}