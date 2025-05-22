<?php

namespace App\Report\Builder\Sensors;

use App\Entity\Vehicle;
use App\Report\ReportBuilder;
use App\Service\Report\ReportMapper;

class DigitalIOReportBuilder extends ReportBuilder
{
    public const REPORT_TYPE = ReportMapper::TYPE_DIGITAL_IO;
    public const REPORT_TEMPLATE = 'reports/digital_io.html.twig';

    public function getJson()
    {
        $pagination = $this->paginator->paginate($this->generateData(), $this->page, $this->limit);
        $pagination->setItems(
            $this->vehicleService->replaceNestedArrayKeysToCamelCase($pagination->getItems())
        );

        return $pagination;
    }

    public function getPdf()
    {
        return SensorReportHelper::prepareIOExportData($this->generateData(), $this->params, $this->translator, $this->user);
    }

    public function getCsv()
    {
        return SensorReportHelper::prepareIOExportData($this->generateData(), $this->params, $this->translator, $this->user);
    }

    public function generateData()
    {
        $vehiclesList = $this->vehicleService->vehicleList($this->params, $this->user, false, ['id']);

        if (!$vehiclesList) {
            return [];
        }

        $vehicleIds = array_map(function (Vehicle $vehicle) {
            return $vehicle->getId();
        }, $vehiclesList);
        $this->params['vehicleIds'] = $vehicleIds;
        $this->params['teamId'] = $this->user->getTeamId();

        return $this->emSlave->getRepository(Vehicle::class)
            ->vehiclesIOListQuery(SensorReportHelper::prepareFieldsForReportByVehicleIO($this->params));
    }
}