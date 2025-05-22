<?php

namespace App\Report\Builder\Summary;

use App\Entity\BaseEntity;
use App\Entity\Vehicle;
use App\Report\Builder\DrivingBehaviour\DrivingBehaviourReportHelper;
use App\Report\ReportBuilder;
use App\Service\Report\ReportMapper;
use App\Util\RequestFilterResolver\RequestFilterResolver;

class VehicleSummaryReportBuilder extends ReportBuilder
{
    public const REPORT_TYPE = ReportMapper::TYPE_VEHICLE_SUMMARY;

    public function getJson()
    {
        return $this->generateData();
    }

    public function getPdf()
    {
        return SummaryReportHelper::prepareExportData(
            $this->generateData()->execute()->fetchAll(), $this->params, $this->translator
        );
    }

    public function getCsv()
    {
        return SummaryReportHelper::prepareExportData(
            $this->generateData()->execute()->fetchAll(), $this->params, $this->translator
        );
    }

    public function generateData()
    {
        $resolvedParams = RequestFilterResolver::resolve($this->params);
        $params = $resolvedParams + $this->params;
        $elasticParams = SummaryReportHelper::getVehicleElasticSearchParams($params);
        $elasticParams['status'] = Vehicle::REPORT_STATUSES;
        $vehicles = $this->vehicleService->vehicleList($elasticParams, $this->user, false);
        $params['vehicles'] = array_values(DrivingBehaviourReportHelper::buildExcessiveSpeedMap($vehicles));
        $params['excSpeedMap'] = DrivingBehaviourReportHelper::buildExcessiveSpeedMap($vehicles);

        return $this->emSlave->getRepository(Vehicle::class)->getVehiclesSummary($params);
    }
}