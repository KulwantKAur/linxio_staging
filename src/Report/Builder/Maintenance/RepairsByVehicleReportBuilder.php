<?php

namespace App\Report\Builder\Maintenance;

use App\Entity\ServiceRecord;
use App\Report\ReportBuilder;
use App\Service\Report\ReportMapper;

class RepairsByVehicleReportBuilder extends ReportBuilder
{
    use ServiceRecordDetailedTrait;

    public const REPORT_TYPE = ReportMapper::TYPE_REPAIRS_BY_VEHICLE;
    public const REPORT_TEMPLATE = 'reports/repairs-by-vehicle.html.twig';

    public function getJson()
    {
        $this->params['fields'] = array_merge(ServiceRecord::DEFAULT_FIELDS, ['vehicleDetailed']);

        return $this->detailedReport($this->params, $this->user, ServiceRecord::TYPE_REPAIR);
    }

    public function getPdf()
    {
        return $this->detailedReportByVehicle($this->params, $this->user, $this->translator, ServiceRecord::TYPE_REPAIR);
    }

    public function getCsv()
    {
        return MaintenanceReportHelper::prepareExportData(
            $this->detailedReport($this->params, $this->user, ServiceRecord::TYPE_REPAIR)->execute()->fetchAll(),
            $this->params, $this->user, $this->translator
        );
    }
}