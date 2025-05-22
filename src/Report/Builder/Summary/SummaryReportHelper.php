<?php

namespace App\Report\Builder\Summary;

use App\Entity\BaseEntity;
use App\Entity\Route;
use App\Entity\User;
use App\Entity\UserGroup;
use App\Enums\SqlEntityFields;
use App\Report\Builder\Route\RouteReportHelper;
use App\Service\User\UserServiceHelper;
use App\Util\ArrayHelper;
use App\Util\DateHelper;
use App\Util\MetricHelper;
use App\Util\TranslateHelper;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class SummaryReportHelper
{
    public const FIELDS_IN_SECONDS = [
        'parking_time',
        'total_parking_time',
        'parking_time_total',
        'driving_time',
        'total_driving_time',
        'driving_time_total',
        'total_duration',
        'work_duration',
        'private_duration',
        'idling_time',
        'idling_time_total',
        'work_duration_total',
        'private_duration_total',
        'total_duration_total',
        'parking_time_total',
        'parkingTime',
        'drivingTime',
        'durationTotal',
        'duration',
        'idlingTime'
    ];
    public const FIELDS_IN_METRES = [
        'distance',
        'total_distance',
        'distance_total',
        'start_odometer',
        'finish_odometer',
        'end_odometer',
        'total_distance',
        'work_distance',
        'total_work_distance',
        'private_distance',
        'total_private_distance',
        'total_distance_total',
        'startOdometer',
        'endOdometer',
        'privateDistance',
        'workDistance',
        'distanceTotal'
    ];

    public const FIELDS_ENGINE_HOURS = [
        "minEngineOnTime",
        "maxEngineOnTime",
        "engineOnTime",
        "min_engine_on_time",
        "max_engine_on_time",
        "engine_on_time",
        "total_engine_on_time",
    ];

    public static function getVehicleElasticSearchParams(array $params): array
    {
        if (empty($params['vehicleIds'])) {
            unset($params['vehicleIds']);
        }

        return array_intersect_key(
            $params,
            array_flip(['defaultLabel', 'regNo', 'depot', 'groups', 'id', 'vehicleIds', 'caseOr', 'driverIdForHistoryVehicles'])
        );
    }

    public static function prepareExportData($data, $params, TranslatorInterface $translator, $toCamelCase = true)
    {
        $results = [];
        if ($toCamelCase) {
            $data = ArrayHelper::keysToCamelCase($data);
        }
        foreach ($data as $item) {
            $item = DateHelper::fieldsToPeriod((array)$item, self::FIELDS_IN_SECONDS);
            $item = MetricHelper::fieldsToKm($item, self::FIELDS_IN_METRES);
            $item = DateHelper::fieldsToHours($item, self::FIELDS_ENGINE_HOURS);

            $results[] = $item;
        }

        return TranslateHelper::translateEntityArrayForExport(
            $results, $translator, $params['fields'], 'vehicle_summary'
        );
    }

    public static function prepareExportDataByVehicle(
        $vehiclesData,
        $params,
        TranslatorInterface $translator,
        $toCamelCase = true
    ): array {
        $resultData = [];
        foreach ($vehiclesData as $key => $data) {
            $resultData[$key] = self::prepareExportData($data, $params, $translator, $toCamelCase);

            $data[0]->total = DateHelper::fieldsToPeriod($data[0]->total, self::FIELDS_IN_SECONDS);
            $data[0]->total = DateHelper::fieldsToHours($data[0]->total, self::FIELDS_ENGINE_HOURS);
            $data[0]->total = MetricHelper::fieldsToKm($data[0]->total, self::FIELDS_IN_METRES);
            $resultData[$key][1]['total'] = $data[0]->total;
        }

        return $resultData;
    }

    public static function prepareDriverSummaryExportData(
        $routes,
        $requestData,
        User $user,
        TranslatorInterface $translator,
        $totalFields = []
    ) {
        $results = [];
        $routes = $routes->execute()->fetchAll();
        $fields = array_merge($requestData['fields'] ?? [], $totalFields);

        foreach ($routes as $route) {
            $results[] = self::formatReportFields($route, $user);
        }
        if (in_array(SqlEntityFields::STARTED_AT, $fields)) {
            $fields[] = SqlEntityFields::STARTED_AT_DATE;
            $fields[] = SqlEntityFields::STARTED_AT_TIME;
            $fields = ArrayHelper::removeFromArrayByValue(SqlEntityFields::STARTED_AT, $fields);
        }
        if (in_array(SqlEntityFields::FINISHED_AT, $fields)) {
            $fields[] = SqlEntityFields::FINISHED_AT_DATE;
            $fields[] = SqlEntityFields::FINISHED_AT_TIME;
            $fields = ArrayHelper::removeFromArrayByValue(SqlEntityFields::FINISHED_AT, $fields);
        }

        return TranslateHelper::translateEntityArrayForExport($results, $translator, $fields, Route::class);
    }

    public static function formatReportFields(array $route, User $user)
    {
        $timeZone = $user->getTimezone();
        $dateFormat = $user->getDateFormatSettingConverted();
        $timeFormat = $user->getTimeFormatSetting();

        $route = DateHelper::fieldsToPeriod($route, self::FIELDS_IN_SECONDS);
        $route = MetricHelper::fieldsToKm($route, self::FIELDS_IN_METRES);

        if (isset($route[SqlEntityFields::STARTED_AT])) {
            $date = DateHelper::formatDate(
                $route[SqlEntityFields::STARTED_AT], $dateFormat, $timeZone
            );
            $time = DateHelper::formatDate(
                $route[SqlEntityFields::STARTED_AT], $timeFormat, $timeZone
            );
            $route = ArrayHelper::arraySpliceAfterKey($route, SqlEntityFields::STARTED_AT,
                [SqlEntityFields::STARTED_AT_DATE => $date, SqlEntityFields::STARTED_AT_TIME => $time]);
        }

        if (isset($route[SqlEntityFields::FINISHED_AT])) {
            $date = DateHelper::formatDate(
                $route[SqlEntityFields::FINISHED_AT], $dateFormat, $timeZone
            );
            $time = DateHelper::formatDate(
                $route[SqlEntityFields::FINISHED_AT], $timeFormat, $timeZone
            );
            $route = ArrayHelper::arraySpliceAfterKey($route, SqlEntityFields::FINISHED_AT,
                [SqlEntityFields::FINISHED_AT_DATE => $date, SqlEntityFields::FINISHED_AT_TIME => $time]);
        }

        if (isset($route['defaultlabel'])) {
            $route = array_merge(['defaultLabel' => $route['defaultlabel']], $route);
            unset($route['defaultlabel']);
        }

        return $route;
    }

    public static function getDriverSummaryReportByVehicle(
        $params,
        User $user,
        $data,
        TranslatorInterface $translator,
        EntityManagerInterface $em
    ) {
        $result = [];
        $drivers = self::driverSummaryDriversList($params, $user, $em)->execute()->fetchAll();

        unset($params['driver_name']);

        foreach ($drivers as $driver) {
            $params['driver_id'] = $driver['driver_id'];
            $result['drivers'][] = [
                'driver' => $driver,
                'data' => self::prepareDriverSummaryExportData($data, $params, $user, $translator,
                    [
                        'work_duration_total',
                        'private_duration_total',
                        'total_distance_total',
                        'total_duration_total',
                        'max_speed_total',
                        'stops_count_total',
                        'parking_time_total',
                        'speeding_events_count_total',
                        'eco_drive_events_total',
                        'eco_drive_score_total'
                    ])
            ];
        }

        return $result;
    }

    public static function driverSummaryDriversList(array $params, User $user, EntityManagerInterface $entityManager)
    {
        $params = UserServiceHelper::handleTeamParams($params, $user);
        $params['sort'] = 'regno';

        if ($user->needToCheckUserGroup()) {
            $vehicleIds = $entityManager->getRepository(UserGroup::class)->getUserVehiclesIdFromUserGroup($user);
            $params['vehicleIds'] = $vehicleIds;
        }

        return $entityManager->getRepository(Route::class)->getDriverListSummary(RouteReportHelper::prepareFields($params));
    }
}