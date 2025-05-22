<?php

namespace App\Report\Builder\Maintenance;

use App\Entity\ServiceRecord;
use App\Report\ReportBuilder;
use App\Service\Report\ReportMapper;

class ServiceRecordsDetailedReportBuilder extends ReportBuilder
{
    use ServiceRecordDetailedTrait;

    public const REPORT_TYPE = ReportMapper::TYPE_SERVICE_RECORDS_DETAILED;

    public function getJson()
    {
        return $this->detailedReport($this->params, $this->user, ServiceRecord::TYPE_SERVICE_RECORD);
    }

    public function getPdf()
    {
        $data = $this->detailedReport($this->params, $this->user, ServiceRecord::TYPE_SERVICE_RECORD)
            ->execute()->fetchAll();

        return MaintenanceReportHelper::prepareExportData($data, $this->params, $this->user, $this->translator);
    }

    public function getCsv()
    {
        $data = $this->detailedReport($this->params, $this->user, ServiceRecord::TYPE_SERVICE_RECORD)
            ->execute()->fetchAll();

        return MaintenanceReportHelper::prepareExportData($data, $this->params, $this->user, $this->translator);
    }
}