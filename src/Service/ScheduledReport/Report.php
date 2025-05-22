<?php


namespace App\Service\ScheduledReport;

use App\Entity\ScheduledReport;
use App\Service\Report\ReportMapper;

class Report extends ReportMapper
{
    public const PERIOD_1D = '1d';
    public const PERIOD_2D = '2d';
    public const PERIOD_3D = '3d';
    public const PERIOD_4D = '4d';
    public const PERIOD_5D = '5d';
    public const PERIOD_6D = '6d';
    public const PERIOD_1W = '1w';
    public const PERIOD_2W = '2w';
    public const PERIOD_3W = '3w';
    public const PERIOD_4W = '4w';
    public const PERIOD_1M = '1mo';
    public const PERIOD_2M = '2mo';
    public const PERIOD_3M = '3mo';

    public const PERIOD_1D_FULL = '1 day';
    public const PERIOD_2D_FULL = '2 days';
    public const PERIOD_3D_FULL = '3 days';
    public const PERIOD_4D_FULL = '4 days';
    public const PERIOD_5D_FULL = '5 days';
    public const PERIOD_6D_FULL = '6 days';
    public const PERIOD_1W_FULL = '1 week';
    public const PERIOD_2W_FULL = '2 weeks';
    public const PERIOD_3W_FULL = '3 weeks';
    public const PERIOD_4W_FULL = '4 weeks';
    public const PERIOD_1M_FULL = '1 month';
    public const PERIOD_2M_FULL = '2 months';
    public const PERIOD_3M_FULL = '3 months';

    public const TYPES = [
        self::TYPE_ROUTES,
        self::TYPE_ROUTES_BY_VEHICLE,
        self::TYPE_STOPS,
        self::TYPE_STOPS_BY_VEHICLE,
        self::TYPE_FBT,
        self::TYPE_AREA_SUMMARY,
        self::TYPE_AREA_VISITED,
        self::TYPE_AREA_NOT_VISITED,
        self::TYPE_FUEL_SUMMARY,
        self::TYPE_FUEL_RECORDS,
        self::TYPE_FUEL_RECORDS_BY_VEHICLE,
        self::TYPE_DRIVING_BEHAVIOR_VEHICLE,
        self::TYPE_DRIVING_BEHAVIOR_DRIVER,
        self::TYPE_SPEEDING,
        self::TYPE_MAINTENANCE_SUMMARY,
        self::TYPE_SERVICE_RECORDS_SUMMARY,
        self::TYPE_SERVICE_RECORDS_DETAILED,
        self::TYPE_SERVICE_RECORDS_BY_VEHICLE,
        self::TYPE_REPAIRS_BY_VEHICLE,
        self::TYPE_MAINTENANCE_TOTAL,
        self::TYPE_MAINTENANCE_TOTAL_BY_VEHICLE,
        self::TYPE_VEHICLE_SUMMARY,
        self::TYPE_DRIVER_SUMMARY,
        self::TYPE_VEHICLE_INSPECTION,
        self::TYPE_TEMPERATURE_BY_VEHICLE,
        self::TYPE_TEMPERATURE_BY_SENSOR,
        self::TYPE_DIGITAL_IO,
    ];

    public const PERIOD_DAY = [
        self::PERIOD_1D_FULL,
        self::PERIOD_2D_FULL,
        self::PERIOD_3D_FULL,
        self::PERIOD_4D_FULL,
        self::PERIOD_5D_FULL,
        self::PERIOD_6D_FULL,
    ];

    public const PERIOD_WEEK = [
        self::PERIOD_1W_FULL,
        self::PERIOD_2W_FULL,
        self::PERIOD_3W_FULL,
        self::PERIOD_4W_FULL,
    ];

    public const PERIOD_MONTH = [
        self::PERIOD_1M_FULL,
        self::PERIOD_2M_FULL,
        self::PERIOD_3M_FULL
    ];

    public const TIME = [
        '0:00',
        '0:30',
        '1:00',
        '1:30',
        '2:00',
        '2:30',
        '3:00',
        '3:30',
        '4:00',
        '4:30',
        '5:00',
        '5:30',
        '6:00',
        '6:30',
        '7:00',
        '7:30',
        '8:00',
        '8:30',
        '9:00',
        '9:30',
        '10:00',
        '10:30',
        '11:00',
        '11:30',
        '12:00',
        '12:30',
        '13:00',
        '13:30',
        '14:00',
        '14:30',
        '15:00',
        '15:30',
        '16:00',
        '16:30',
        '17:00',
        '17:30',
        '18:00',
        '18:30',
        '19:00',
        '19:30',
        '20:00',
        '20:30',
        '21:00',
        '21:30',
        '22:00',
        '22:30',
        '23:00',
        '23:30',
    ];

    public const WEEK = [
        1 => 'Monday',
        2 => 'Tuesday',
        3 => 'Wednesday',
        4 => 'Thursday',
        5 => 'Friday',
        6 => 'Saturday',
        0 => 'Sunday',
    ];

    public const DAYS = [
        1,
        2,
        3,
        4,
        5,
        6,
        7,
        8,
        9,
        10,
        11,
        12,
        13,
        14,
        15,
        16,
        17,
        18,
        19,
        20,
        21,
        22,
        23,
        24,
        25,
        26,
        27,
        28,
        29,
        30,
        31
    ];

    public const REPORT_UI_SCHEME = [
        'interval' => [
            'type' => [
                ScheduledReport::INTERVAL_DAILY => [
                    'period' => self::PERIOD_DAY
                ],
                ScheduledReport::INTERVAL_WEEKLY => [
                    'day' => self::WEEK,
                    'period' => self::PERIOD_WEEK
                ],
                ScheduledReport::INTERVAL_MONTHLY => [
                    'period' => self::PERIOD_MONTH,
                    'day' => self::DAYS,
                ]
            ],
            'time' => self::TIME
        ],
        'type' => self::TYPES
    ];

    public static function periodMapper($period): ?string
    {
        switch ($period) {
            case self::PERIOD_1D_FULL:
                return self::PERIOD_1D;
            case self::PERIOD_2D_FULL:
                return self::PERIOD_2D;
            case self::PERIOD_3D_FULL:
                return self::PERIOD_3D;
            case self::PERIOD_4D_FULL:
                return self::PERIOD_4D;
            case self::PERIOD_5D_FULL:
                return self::PERIOD_5D;
            case self::PERIOD_6D_FULL:
                return self::PERIOD_6D;
            case self::PERIOD_1W_FULL:
                return self::PERIOD_1W;
            case self::PERIOD_2W_FULL:
                return self::PERIOD_2W;
            case self::PERIOD_3W_FULL:
                return self::PERIOD_3W;
            case self::PERIOD_4W_FULL:
                return self::PERIOD_4W;
            case self::PERIOD_1M_FULL:
                return self::PERIOD_1M;
            case self::PERIOD_2M_FULL:
                return self::PERIOD_2M;
            case self::PERIOD_3M_FULL:
                return self::PERIOD_3M;
            default:
                return null;
        }
    }
}