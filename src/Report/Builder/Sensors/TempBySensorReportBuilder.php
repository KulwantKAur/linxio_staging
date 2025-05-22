<?php

namespace App\Report\Builder\Sensors;

use App\Report\ReportBuilder;
use App\Service\BaseService;
use App\Service\Report\ReportMapper;

class TempBySensorReportBuilder extends ReportBuilder
{
    public const REPORT_TYPE = ReportMapper::TYPE_TEMPERATURE_BY_SENSOR;
    public const REPORT_TEMPLATE = 'reports/report-by-vehicle.html.twig';

    public function getJson()
    {
        $query = $this->deviceSensorService
            ->getDeviceTempAndHumiditySensorReportQueryBySensor($this->params, $this->user);
        $pagination = $this->paginator->paginate($query, $this->page, ($this->limit == 0) ? 1 : $this->limit);
        if ($this->limit == 0 && $pagination->getTotalItemCount() > 0) {
            $pagination = $this->paginator->paginate($query, 1, $pagination->getTotalItemCount());
        }

        $pagination->setItems(BaseService::replaceNestedArrayKeysToCamelCase($pagination->getItems()));

        return $pagination;
    }

    public function getPdf()
    {
        return $this->deviceSensorService->getDeviceTempAndHumiditySensorReportQueryBySensorPdf(
            $this->params,
            $this->user
        );
    }

    public function getCsv()
    {
        $this->params['fields'][] = 'deviceSensorLabel';
        $this->params['fields'][] = 'deviceSensorBleId';

        return $this->deviceSensorService->getTemperatureBySensorExportData($this->params, $this->user);
    }
}