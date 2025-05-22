<?php

namespace App\Report\Core\Factory;

use App\Report\Builder\Area\AreaNotVisitedReportBuilder;
use App\Report\Builder\Area\AreaSummaryReportBuilder;
use App\Report\Builder\Area\AreaVisitedReportBuilder;
use App\Report\Builder\Billing\BillingPaymentsReportBuilder;
use App\Report\Builder\DrivingBehaviour\DrivingBehaviourDriverReportBuilder;
use App\Report\Builder\DrivingBehaviour\DrivingBehaviourVehicleReportBuilder;
use App\Report\Builder\DrivingBehaviour\SpeedingReportBuilder;
use App\Report\Builder\EventLog\EventLogReportBuilder;
use App\Report\Builder\Fbt\FbtBusinessDriverReportBuilder;
use App\Report\Builder\Fbt\FbtBusinessVehicleReportBuilder;
use App\Report\Builder\Fbt\FbtDriverReportBuilder;
use App\Report\Builder\Fuel\FuelRecordsByVehicleReportBuilder;
use App\Report\Builder\Fuel\FuelRecordsReportBuilder;
use App\Report\Builder\Fuel\FuelSummaryReportBuilder;
use App\Report\Builder\Maintenance\MaintenanceSummaryReportBuilder;
use App\Report\Builder\Maintenance\MaintenanceTotalByVehicleReportBuilder;
use App\Report\Builder\Maintenance\MaintenanceTotalReportBuilder;
use App\Report\Builder\Maintenance\RepairsByVehicleReportBuilder;
use App\Report\Builder\Maintenance\RepairsDetailedReportBuilder;
use App\Report\Builder\Maintenance\ServiceRecordsByVehicleReportBuilder;
use App\Report\Builder\Maintenance\ServiceRecordsDetailedReportBuilder;
use App\Report\Builder\Maintenance\ServiceRecordsSummaryReportBuilder;
use App\Report\Builder\Fbt\FbtVehicleReportBuilder;
use App\Report\Builder\Route\RouteByVehicleReportBuilder;
use App\Report\Builder\Sensors\DigitalIOReportBuilder;
use App\Report\Builder\Sensors\TempBySensorReportBuilder;
use App\Report\Builder\Sensors\TempByVehicleReportBuilder;
use App\Report\Builder\Summary\DriverSummaryReportBuilder;
use App\Report\Builder\Summary\VehicleDaySummaryReportBuilder;
use App\Report\Builder\Summary\VehicleInspectionReportBuilder;
use App\Report\Builder\Summary\VehicleSummaryReportBuilder;
use App\Report\Core\Exception\UndefinedReportException;
use App\Report\Core\Interfaces\ReportBuilderInterface;
use App\Report\Builder\Route\FbtReportBuilder;
use App\Report\Builder\Route\RouteReportBuilder;
use App\Report\Builder\Route\StopByVehicleReportBuilder;
use App\Report\Builder\Route\StopReportBuilder;
use App\Service\Report\ReportMapper;

class ReportBuilderFactory
{
    /**
     * Modify, when add new report handler
     * @var array
     */
    protected static array $availableReportBuilder = [
        ReportMapper::TYPE_ROUTES => RouteReportBuilder::class,
        ReportMapper::TYPE_ROUTES_BY_VEHICLE => RouteByVehicleReportBuilder::class,
        ReportMapper::TYPE_STOPS => StopReportBuilder::class,
        ReportMapper::TYPE_STOPS_BY_VEHICLE => StopByVehicleReportBuilder::class,
        ReportMapper::TYPE_FBT => FbtReportBuilder::class,
        ReportMapper::TYPE_FBT_VEHICLE => FbtVehicleReportBuilder::class,
        ReportMapper::TYPE_FBT_DRIVER => FbtDriverReportBuilder::class,
        ReportMapper::TYPE_AREA_SUMMARY => AreaSummaryReportBuilder::class,
        ReportMapper::TYPE_AREA_VISITED => AreaVisitedReportBuilder::class,
        ReportMapper::TYPE_AREA_NOT_VISITED => AreaNotVisitedReportBuilder::class,
        ReportMapper::TYPE_DRIVING_BEHAVIOR_VEHICLE => DrivingBehaviourVehicleReportBuilder::class,
        ReportMapper::TYPE_DRIVING_BEHAVIOR_DRIVER => DrivingBehaviourDriverReportBuilder::class,
        ReportMapper::TYPE_SPEEDING => SpeedingReportBuilder::class,
        ReportMapper::TYPE_MAINTENANCE_SUMMARY => MaintenanceSummaryReportBuilder::class,
        ReportMapper::TYPE_SERVICE_RECORDS_SUMMARY => ServiceRecordsSummaryReportBuilder::class,
        ReportMapper::TYPE_SERVICE_RECORDS_DETAILED => ServiceRecordsDetailedReportBuilder::class,
        ReportMapper::TYPE_SERVICE_RECORDS_BY_VEHICLE => ServiceRecordsByVehicleReportBuilder::class,
        ReportMapper::TYPE_REPAIRS_BY_VEHICLE => RepairsByVehicleReportBuilder::class,
        ReportMapper::TYPE_REPAIRS_DETAILED => RepairsDetailedReportBuilder::class,
        ReportMapper::TYPE_MAINTENANCE_TOTAL => MaintenanceTotalReportBuilder::class,
        ReportMapper::TYPE_MAINTENANCE_TOTAL_BY_VEHICLE => MaintenanceTotalByVehicleReportBuilder::class,
        ReportMapper::TYPE_DRIVER_SUMMARY => DriverSummaryReportBuilder::class,
        ReportMapper::TYPE_VEHICLE_INSPECTION => VehicleInspectionReportBuilder::class,
        ReportMapper::TYPE_VEHICLE_SUMMARY => VehicleSummaryReportBuilder::class,
        ReportMapper::TYPE_FUEL_SUMMARY => FuelSummaryReportBuilder::class,
        ReportMapper::TYPE_FUEL_RECORDS => FuelRecordsReportBuilder::class,
        ReportMapper::TYPE_FUEL_RECORDS_BY_VEHICLE => FuelRecordsByVehicleReportBuilder::class,
        ReportMapper::TYPE_TEMPERATURE_BY_SENSOR => TempBySensorReportBuilder::class,
        ReportMapper::TYPE_TEMPERATURE_BY_VEHICLE => TempByVehicleReportBuilder::class,
        ReportMapper::TYPE_DIGITAL_IO => DigitalIOReportBuilder::class,
        ReportMapper::TYPE_VEHICLE_DAY_SUMMARY => VehicleDaySummaryReportBuilder::class,
        ReportMapper::TYPE_FBT_BUSINESS_DRIVER => FbtBusinessDriverReportBuilder::class,
        ReportMapper::TYPE_FBT_BUSINESS_VEHICLE => FbtBusinessVehicleReportBuilder::class,
        ReportMapper::TYPE_BILLING => BillingPaymentsReportBuilder::class,
        ReportMapper::TYPE_EVENT_LOG => EventLogReportBuilder::class,
    ];

    /**
     * @param string $reportName
     * @param array $defaultParams
     * @return ReportBuilderInterface
     * @throws UndefinedReportException
     */
    public function getInstance(string $reportName, array $defaultParams): ReportBuilderInterface
    {
        if (!array_key_exists($reportName, self::$availableReportBuilder)) {
            throw new UndefinedReportException(
                sprintf(
                    'unsupported report builder with name "%s".',
                    $reportName
                )
            );
        }
        $entityBuilder = self::$availableReportBuilder[$reportName];

        return (new $entityBuilder(...$defaultParams));
    }
}
