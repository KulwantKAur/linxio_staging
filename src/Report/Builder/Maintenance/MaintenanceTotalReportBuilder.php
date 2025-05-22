<?php

namespace App\Report\Builder\Maintenance;

use App\Report\ReportBuilder;
use App\Service\Report\ReportMapper;

class MaintenanceTotalReportBuilder extends ReportBuilder
{
    use ServiceRecordDetailedTrait;

    public const REPORT_TYPE = ReportMapper::TYPE_MAINTENANCE_TOTAL;

    public function getJson()
    {
        return $this->detailedReport($this->params, $this->user);
    }

    public function getPdf()
    {
        return MaintenanceReportHelper::prepareExportData(
            $this->detailedReport($this->params, $this->user, null)->execute()->fetchAll(),
            $this->params,
            $this->user,
            $this->translator
        );
    }

    public function getCsv()
    {
        return MaintenanceReportHelper::prepareExportData(
            $this->detailedReport($this->params, $this->user, null)->execute()->fetchAll(),
            $this->params,
            $this->user,
            $this->translator
        );
    }
}