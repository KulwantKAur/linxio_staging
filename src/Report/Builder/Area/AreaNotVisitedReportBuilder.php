<?php

namespace App\Report\Builder\Area;

use App\Entity\AreaHistory;
use App\Report\ReportBuilder;
use App\Service\Report\ReportMapper;
use App\Service\User\UserServiceHelper;
use App\Util\RequestFilterResolver\RequestFilterResolver;

class AreaNotVisitedReportBuilder extends ReportBuilder
{
    public const REPORT_TYPE = ReportMapper::TYPE_AREA_NOT_VISITED;

    public function getJson()
    {
        return $this->generateData()->getResult();
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
        $params = UserServiceHelper::handleTeamParams($resolvedParams + $this->params, $this->user);

        return $this->emSlave->getRepository(AreaHistory::class)->getNotVisitedAreas($params, $this->user);
    }
}