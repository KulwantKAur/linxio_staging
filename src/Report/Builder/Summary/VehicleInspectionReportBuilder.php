<?php

namespace App\Report\Builder\Summary;

use App\Entity\User;
use App\Report\Core\ResponseType\ArrayResponse;
use App\Report\ReportBuilder;
use App\Service\Report\ReportMapper;
use App\Util\TranslateHelper;

class VehicleInspectionReportBuilder extends ReportBuilder
{
    public const REPORT_TYPE = ReportMapper::TYPE_VEHICLE_INSPECTION;

    public function getJson()
    {
        return new ArrayResponse($this->digitalFormAnswerService->getReportVehicleInspection($this->user, $this->params));
    }

    public function getPdf()
    {
        return $this->getReportVehicleInspectionExportData();
    }

    public function getCsv()
    {
        return $this->getReportVehicleInspectionExportData();
    }

    public function getReportVehicleInspectionExportData(): ?array
    {
        $items = $this->digitalFormAnswerService->getReportVehicleInspection($this->user, $this->params, false);

        return TranslateHelper::translateEntityArrayForExport($items, $this->translator, $this->params['fields'] ?? [], user: $this->user);
    }
}