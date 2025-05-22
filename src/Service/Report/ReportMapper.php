<?php

namespace App\Service\Report;

use App\Entity\Plan;

class ReportMapper
{
    public const TYPE_ROUTES = 'routes';
    public const TYPE_ROUTES_BY_VEHICLE = 'routes by vehicle';
    public const TYPE_STOPS = 'stops';
    public const TYPE_STOPS_BY_VEHICLE = 'stops by vehicle';
    public const TYPE_FBT = 'fbt';
    public const TYPE_FBT_VEHICLE = 'fbt vehicle';
    public const TYPE_FBT_DRIVER = 'fbt driver';
    public const TYPE_FBT_BUSINESS_DRIVER = 'fbt business driver';
    public const TYPE_FBT_BUSINESS_VEHICLE = 'fbt business vehicle';

    public const TYPE_AREA_SUMMARY = 'area summary';
    public const TYPE_AREA_VISITED = 'area visited';
    public const TYPE_AREA_NOT_VISITED = 'area not visited';

    public const TYPE_FUEL_SUMMARY = 'fuel summary';
    public const TYPE_FUEL_RECORDS = 'fuel records';
    public const TYPE_FUEL_RECORDS_BY_VEHICLE = 'fuel records by vehicle';

    public const TYPE_DRIVING_BEHAVIOR_VEHICLE = 'driving behaviour vehicle';
    public const TYPE_DRIVING_BEHAVIOR_DRIVER = 'driving behaviour driver';

    public const TYPE_SPEEDING = 'speeding';

    public const TYPE_MAINTENANCE_SUMMARY = 'maintenance summary';
    public const TYPE_SERVICE_RECORDS_SUMMARY = 'service records summary';
    public const TYPE_SERVICE_RECORDS_DETAILED = 'service records detailed';
    public const TYPE_SERVICE_RECORDS_BY_VEHICLE = 'service records by vehicle';
    public const TYPE_REPAIRS_BY_VEHICLE = 'repairs by vehicle';
    public const TYPE_REPAIRS_DETAILED = 'repairs detailed';
    public const TYPE_MAINTENANCE_TOTAL = 'maintenance total';
    public const TYPE_MAINTENANCE_TOTAL_BY_VEHICLE = 'maintenance total by vehicle';

    public const TYPE_VEHICLE_SUMMARY = 'vehicle summary';
    public const TYPE_DRIVER_SUMMARY = 'driver summary';
    public const TYPE_VEHICLE_INSPECTION = 'vehicle inspection';

    public const TYPE_TEMPERATURE_BY_VEHICLE = 'temperature by vehicle';
    public const TYPE_TEMPERATURE_BY_SENSOR = 'temperature by sensor';
    public const TYPE_DIGITAL_IO = 'digital i/o';

    public const REPORTS_ROUTES = 'reports_routes';
    public const REPORTS_AREAS = 'reports_areas';
    public const REPORTS_FUEL = 'reports_fuel';
    public const REPORTS_DRIVING_BEHAVIOR = 'reports_driving_behaviour';
    public const REPORTS_MAINTENANCE = 'reports_maintenance';
    public const REPORTS_SUMMARY = 'reports_summary';
    public const REPORTS_SENSORS = 'reports_sensors';
    public const REPORTS_FBT = 'business_private_fbt';

    public const TYPE_VEHICLE_DAY_SUMMARY = 'vehicle day summary';

    public const TYPE_BILLING = 'billing';

    public const TYPE_EVENT_LOG = 'event log';

    public const REPORTS_BY_PLAN = [
        self::REPORTS_ROUTES => [
            self::TYPE_ROUTES => [Plan::PLAN_ESSENTIALS, Plan::PLAN_PLUS],
            self::TYPE_ROUTES_BY_VEHICLE => [Plan::PLAN_ESSENTIALS, Plan::PLAN_PLUS],
            self::TYPE_STOPS => [Plan::PLAN_ESSENTIALS, Plan::PLAN_PLUS],
            self::TYPE_STOPS_BY_VEHICLE => [Plan::PLAN_ESSENTIALS, Plan::PLAN_PLUS]
        ],
        self::REPORTS_AREAS => [
            self::TYPE_AREA_SUMMARY => [Plan::PLAN_ESSENTIALS, Plan::PLAN_PLUS],
            self::TYPE_AREA_VISITED => [Plan::PLAN_ESSENTIALS, Plan::PLAN_PLUS],
            self::TYPE_AREA_NOT_VISITED => [Plan::PLAN_ESSENTIALS, Plan::PLAN_PLUS],
        ],

        self::REPORTS_FUEL => [
            self::TYPE_FUEL_SUMMARY => [Plan::PLAN_PLUS],
            self::TYPE_FUEL_RECORDS => [Plan::PLAN_PLUS],
            self::TYPE_FUEL_RECORDS_BY_VEHICLE => [Plan::PLAN_PLUS],
        ],

        self::REPORTS_DRIVING_BEHAVIOR => [
            self::TYPE_DRIVING_BEHAVIOR_VEHICLE => [Plan::PLAN_PLUS],
            self::TYPE_DRIVING_BEHAVIOR_DRIVER => [Plan::PLAN_PLUS],
            self::TYPE_SPEEDING => [Plan::PLAN_PLUS],
        ],

        self::REPORTS_MAINTENANCE => [
            self::TYPE_MAINTENANCE_SUMMARY => [Plan::PLAN_PLUS],
            self::TYPE_SERVICE_RECORDS_SUMMARY => [Plan::PLAN_PLUS],
            self::TYPE_SERVICE_RECORDS_DETAILED => [Plan::PLAN_PLUS],
            self::TYPE_SERVICE_RECORDS_BY_VEHICLE => [Plan::PLAN_PLUS],
            self::TYPE_REPAIRS_BY_VEHICLE => [Plan::PLAN_PLUS],
            self::TYPE_MAINTENANCE_TOTAL => [Plan::PLAN_PLUS],
            self::TYPE_MAINTENANCE_TOTAL_BY_VEHICLE => [Plan::PLAN_PLUS],
        ],

        self::REPORTS_SUMMARY => [
            self::TYPE_VEHICLE_SUMMARY => [Plan::PLAN_STARTER, Plan::PLAN_ESSENTIALS, Plan::PLAN_PLUS],
            self::TYPE_VEHICLE_DAY_SUMMARY => [Plan::PLAN_STARTER, Plan::PLAN_ESSENTIALS, Plan::PLAN_PLUS],
            self::TYPE_DRIVER_SUMMARY => [Plan::PLAN_ESSENTIALS, Plan::PLAN_PLUS],
            self::TYPE_VEHICLE_INSPECTION => [Plan::PLAN_PLUS],
        ],

        self::REPORTS_SENSORS => [
            self::TYPE_TEMPERATURE_BY_VEHICLE => [Plan::PLAN_ESSENTIALS, Plan::PLAN_PLUS],
            self::TYPE_TEMPERATURE_BY_SENSOR => [Plan::PLAN_ESSENTIALS, Plan::PLAN_PLUS],
            self::TYPE_DIGITAL_IO => [Plan::PLAN_ESSENTIALS, Plan::PLAN_PLUS]
        ],

        self::REPORTS_FBT => [
            self::TYPE_FBT => [Plan::PLAN_ESSENTIALS, Plan::PLAN_PLUS],
            self::TYPE_FBT_VEHICLE => [Plan::PLAN_ESSENTIALS, Plan::PLAN_PLUS],
            self::TYPE_FBT_DRIVER => [Plan::PLAN_ESSENTIALS, Plan::PLAN_PLUS],
        ],
    ];

    public const CUSTOM_REPORTS_PATH = [
        self::TYPE_FBT_BUSINESS_DRIVER => self::REPORTS_FBT,
        self::TYPE_FBT_BUSINESS_VEHICLE => self::REPORTS_FBT,
    ];

    public static function getReportsByPlan(Plan $plan): array
    {
        $reports = [];
        foreach (self::REPORTS_BY_PLAN as $groupKey => $group) {
            foreach ($group as $reportKey => $report) {
                if (in_array($plan->getName(), $report)) {
                    $reports[$groupKey][] = $reportKey;
                }
            }
        }

        return $reports;
    }

    public static function mergeReports(array $byPlan, array $custom): array
    {
        foreach ($custom as $customReport) {
            $byPlan[self::CUSTOM_REPORTS_PATH[$customReport]][] = $customReport;
        }

        return $byPlan;
    }
}