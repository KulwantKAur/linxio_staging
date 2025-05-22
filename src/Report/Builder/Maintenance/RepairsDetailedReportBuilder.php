<?php

namespace App\Report\Builder\Maintenance;

use App\Entity\ServiceRecord;
use App\Report\ReportBuilder;
use App\Service\Report\ReportMapper;

class RepairsDetailedReportBuilder extends ReportBuilder
{
    use ServiceRecordDetailedTrait;

    public const REPORT_TYPE = ReportMapper::TYPE_REPAIRS_DETAILED;

    public function getJson()
    {
        $this->params['fields'] = array_merge(ServiceRecord::DEFAULT_FIELDS, ['vehicleDetailed']);

        return $this->detailedReport($this->params, $this->user, ServiceRecord::TYPE_REPAIR);
    }

    public function getPdf()
    {
        return MaintenanceReportHelper::prepareExportData(
            $this->detailedReport($this->params, $this->user, ServiceRecord::TYPE_REPAIR,)->execute()->fetchAll(),
            $this->params, $this->user, $this->translator //, ['sr_amount_total']
        );
    }

    public function getCsv()
    {
        return MaintenanceReportHelper::prepareExportData(
            $this->detailedReport($this->params, $this->user, ServiceRecord::TYPE_REPAIR,)->execute()->fetchAll(),
            $this->params, $this->user, $this->translator
        );
    }
}