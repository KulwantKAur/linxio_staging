<?php

namespace App\Report\Builder\Maintenance;

use App\Entity\ServiceRecord;
use App\Report\ReportBuilder;
use App\Service\Report\ReportMapper;

class ServiceRecordsByVehicleReportBuilder extends ReportBuilder
{
    use ServiceRecordDetailedTrait;

    public const REPORT_TYPE = ReportMapper::TYPE_SERVICE_RECORDS_BY_VEHICLE;
    public const REPORT_TEMPLATE = 'reports/service-records-detailed-by-vehicle.html.twig';

    public function getJson()
    {
        return $this->detailedReport($this->params, $this->user, ServiceRecord::TYPE_SERVICE_RECORD);
    }

    public function getPdf()
    {
        return $this->detailedReportByVehicle(
            $this->params, $this->user, $this->translator, ServiceRecord::TYPE_SERVICE_RECORD
        );
    }

    public function getCsv()
    {
        $data = $this->detailedReport($this->params, $this->user,
            ServiceRecord::TYPE_SERVICE_RECORD)->execute()->fetchAll();

        return MaintenanceReportHelper::prepareExportData($data, $this->params, $this->user, $this->translator);
    }
}