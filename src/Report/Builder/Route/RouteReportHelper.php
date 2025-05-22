<?php

namespace App\Report\Builder\Route;

use App\Entity\BaseEntity;
use App\Entity\Route;
use App\Entity\Setting;
use App\Entity\User;
use App\Enums\EntityFields;
use App\Enums\SqlEntityFields;
use App\Service\BaseService;
use App\Util\ArrayHelper;
use App\Util\DateHelper;
use App\Util\MetricHelper;
use App\Util\StringHelper;
use App\Util\TranslateHelper;
use Carbon\Carbon;
use Symfony\Contracts\Translation\TranslatorInterface;

class RouteReportHelper
{
    public const FIELDS_IN_SECONDS = [
        'parking_time',
        'parking_time_total',
        'driving_time',
        'driving_time_total',
        'total_duration',
        'work_duration',
        'unclassified_duration',
        'private_duration',
        'idling_time',
        'idling_time_total',
        'work_duration_total',
        'private_duration_total',
        'total_duration_total',
        'parking_time_total',
        'all_total_duration',
        'all_total_work_duration',
        'all_total_private_duration',
        'all_total_unclassified_duration'
    ];
    public const FIELDS_IN_METRES = [
        'distance',
        'distance_total',
        'start_odometer',
        'finish_odometer',
        'total_distance',
        'work_distance',
        'unclassified_distance',
        'private_distance',
        'total_distance_total',
        'all_total_distance',
        'all_total_work_distance',
        'all_total_private_distance',
        'all_total_unclassified_distance'
    ];

    public static function formatReportFields(array $route, User $user)
    {
        $timeZone = $user->getTimezone();
        $dateFormat = $user->getDateFormatSettingConverted();
        $timeFormat = $user->getTimeFormatSetting();

        foreach ($route as $key => $value) {
            if (in_array($key, self::FIELDS_IN_SECONDS)) {
                $route[$key] = DateHelper::seconds2period($value);
            }
            if (in_array($key, self::FIELDS_IN_METRES)) {
                if ($value) {
                    $route[$key] = MetricHelper::metersToKm($value);
                }
            }
        }

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

    /**
     * @param array $data
     * @return array
     */
    public static function prepareFields(array $data)
    {
        $data[EntityFields::START_DATE] =
            isset($data[EntityFields::START_DATE]) ? BaseService::parseDateToUTC($data[EntityFields::START_DATE]) : Carbon::now();
        $data[EntityFields::END_DATE] = isset($data[EntityFields::END_DATE])
            ? BaseService::parseDateToUTC($data[EntityFields::END_DATE])
            : (new Carbon())->subHours(24);

        $data['areaFrom'] = $data['start_areas_name'] ?? null;
        $data['areaTo'] = $data['finish_areas_name'] ?? null;
        $data['vehicleDefaultLabel'] = $data[SqlEntityFields::DEFAULT_LABEL] ?? null;
        $data['defaultLabel'] = $data['defaultLabel'] ?? null;
        $data['vehicleRegNo'] = $data['regno'] ?? null;
        $data['driver'] = $data['driver_name'] ?? null;
        $data['driverId'] = $data['driver_id'] ?? $data['driverId'] ?? null;
        $data['vehicleGroup'] = $data['groups'] ?? null;
        $data['scope'] = $data['scope'] ?? null;
        $data['vehicleDepot'] = $data['depot_name'] ?? null;
        $data['depotId'] = null;
        $data['noDepot'] = false;
        $data['noGroups'] = false;
        $data['areas'] = $data['areas_name'] ?? null;
        $data['vehicleId'] = $data['vehicleId'] ?? null;
        $data['vehicleIds'] = empty($data['vehicleIds']) ? null : $data['vehicleIds'];
        $data['teamId'] = $data['teamId'] ?? null;
        $data['order'] = StringHelper::getOrder($data);
        $data['sort'] = StringHelper::getSort($data, 'route_id');

        if (isset($data['groups']) && is_array($data['groups'])) {
            $data['vehicleGroup'] = implode(', ', array_diff($data['groups'], [null]));
            $data['noGroups'] = in_array(null, $data['groups'], true);
        }

        if (isset($data['depot']) && is_array($data['depot'])) {
            $data['depotId'] = implode(', ', array_diff($data['depot'], [null]));
            $data['noDepot'] = in_array(null, $data['depot'], true);
        }

        return $data;
    }

    public static function prepareRouteReportTotalData($rawData, $convertToHuman = false)
    {
        $data['distance_total'] = array_sum(array_column($rawData, 'distance_total'));
        $data['driving_time_total'] = array_sum(array_column($rawData, 'driving_time_total'));
        $data['idling_time_total'] = array_sum(array_column($rawData, 'idling_time_total'));

        if ($convertToHuman) {
            $data['distance_total'] = MetricHelper::metersToHumanKm($data['distance_total']);
            $data['driving_time_total'] = DateHelper::seconds2human($data['driving_time_total']);
            $data['idling_time_total'] = DateHelper::seconds2human($data['idling_time_total']);
        }

        return $data;
    }

    public static function prepareExportData(
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
            $results[] = RouteReportHelper::formatReportFields($route, $user);
        }
        if (in_array(SqlEntityFields::STARTED_AT, $fields)) {
            $timeFormat = $user->getSettingByName(Setting::TIME_12H)?->getValue();
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

    public static function prepareStopRouteReportTotalData($rawData, $convertToHuman = false)
    {
        $data['parking_time_total'] = array_sum(array_column($rawData, 'parking_time'));
        if ($convertToHuman) {
            $data['parking_time_total'] = DateHelper::seconds2human($data['parking_time_total']);
        }

        return $data;
    }
}