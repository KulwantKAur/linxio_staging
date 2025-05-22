<?php

namespace App\Report\Builder\Maintenance;

use App\Report\ReportBuilder;
use App\Service\Report\ReportMapper;

class MaintenanceTotalByVehicleReportBuilder extends ReportBuilder
{
    use ServiceRecordDetailedTrait;

    public const REPORT_TYPE = ReportMapper::TYPE_MAINTENANCE_TOTAL_BY_VEHICLE;
    public const REPORT_TEMPLATE = 'reports/total-maintenance-by-vehicle.html.twig';

    public function getJson()
    {
        return $this->detailedReport($this->params, $this->user);
    }

    public function getPdf()
    {
        return $this->detailedReportByVehicle($this->params, $this->user, $this->translator, null);
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