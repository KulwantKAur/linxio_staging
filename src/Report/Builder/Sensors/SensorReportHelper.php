<?php

namespace App\Report\Builder\Sensors;

use App\Entity\Vehicle;
use App\Service\BaseService;
use App\Service\Vehicle\VehicleService;
use App\Util\DateHelper;
use App\Util\MetricHelper;
use App\Util\StringHelper;
use App\Util\TranslateHelper;
use Carbon\Carbon;

class SensorReportHelper
{
    public static function prepareFieldsForReportByVehicle(array $data)
    {
        $data['startDate'] = isset($data['startDate']) ? BaseService::parseDateToUTC($data['startDate']) : Carbon::now();
        $data['endDate'] = isset($data['endDate'])
            ? BaseService::parseDateToUTC($data['endDate'])
            : (new Carbon())->subHours(24);
        $data['vehicleDefaultLabel'] = $data['defaultLabel'] ?? null;
        $data['vehicleRegNo'] = $data['regNo'] ?? null;
        $data['vehicleGroup'] = $data['groups'] ?? null;
        $data['vehicleDepot'] = $data['depotName'] ?? null;
        $data['depotId'] = null;
        $data['noDepot'] = false;
        $data['noGroups'] = false;
        $data['vehicleId'] = $data['vehicleId'] ?? null;
        $data['vehicleIds'] = empty($data['vehicleIds']) ? null : $data['vehicleIds'];
        $data['deviceSensorBleId'] = $data['deviceSensorBleId'] ?? null;
        $data['teamId'] = $data['teamId'] ?? null;
        $data['order'] = StringHelper::getOrder($data);
        $data['sort'] = StringHelper::getSort($data, 'device_sensor_id', true);

        if (isset($data['groups']) && is_array($data['groups'])) {
            $data['vehicleGroup'] = implode(', ', array_diff($data['groups'], [null]));
            $data['noGroups'] = in_array(null, $data['groups'], true);
        }
        if (isset($data['depot']) && is_array($data['depot'])) {
            $data['depotId'] = implode(', ', array_diff($data['depot'], [null]));
            $data['noDepot'] = in_array(null, $data['depot'], true);
        }
        $data['sensorId'] = $data['sensorId'] ?? null;

        return $data;
    }

    public static function prepareFieldsForReportBySensor(array $data)
    {
        $data['startDate'] = isset($data['startDate']) ? BaseService::parseDateToUTC($data['startDate']) : Carbon::now();
        $data['endDate'] = isset($data['endDate'])
            ? BaseService::parseDateToUTC($data['endDate'])
            : (new Carbon())->subHours(24);
        $data['sensorId'] = $data['sensorId'] ?? null;
        $data['sensorIds'] = $data['sensorIds'] ?? null;
        $data['sensorBLEId'] = $data['sensorBLEId'] ?? null;
        $data['teamId'] = $data['teamId'] ?? null;
        $data['label'] = $data['label'] ?? null;
        $data['order'] = StringHelper::getOrder($data);
        $data['sort'] = StringHelper::getSort($data, 'device_sensor_id', true);

        return $data;
    }

    public static function prepareFieldsForReportByVehicleIO(array $data)
    {
        $data['startDate'] = isset($data['startDate']) ? BaseService::parseDateToUTC($data['startDate']) : Carbon::now();
        $data['endDate'] = isset($data['endDate'])
            ? BaseService::parseDateToUTC($data['endDate'])
            : (new Carbon())->subHours(24);
        $data['vehicleDefaultLabel'] = $data['defaultLabel'] ?? null;
        $data['vehicleRegNo'] = $data['regNo'] ?? null;
        $data['vehicleGroup'] = $data['groups'] ?? null;
        $data['vehicleDepot'] = $data['depotName'] ?? null;
        $data['depotId'] = null;
        $data['noDepot'] = false;
        $data['noGroups'] = false;
        $data['vehicleId'] = $data['vehicleId'] ?? null;
        $data['vehicleIds'] = empty($data['vehicleIds']) ? null : $data['vehicleIds'];
        $data['teamId'] = $data['teamId'] ?? null;
        $data['driver'] = $data['driverName'] ?? null;
        $data['inputLabel'] = $data['inputLabel'] ?? null;
        $data['inputTypeIds'] = $data['inputTypeIds'] ?? null;
        $data['areaStartId'] = $data['areaStartId'] ?? null;
        $data['areaFinishId'] = $data['areaFinishId'] ?? null;
        $data['order'] = StringHelper::getOrder($data);
        $data['sort'] = StringHelper::getSort($data, 'id', true);

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


    public static function prepareIOExportData($query, $params, $translator, $user): array
    {
        if (!$query) {
            return [];
        }

        $results = [];
        $fields = $params['fields'] ?? Vehicle::REPORT_IO_VALUES;
        $vehicles = BaseService::replaceNestedArrayKeysToCamelCase($query->execute()->fetchAll());
        $timeZone = $user->getTimezone();
        $dateFormat = $user->getDateFormatSettingConverted(true);

        foreach ($vehicles as $result) {
            $result = DateHelper::fieldsToPeriod((array)$result, VehicleService::FIELDS_IN_SECONDS);
            $result = MetricHelper::fieldsToKm($result, VehicleService::FIELDS_IN_METRES);
            if ($result['tsOn'] ?? null) {
                $result['tsOn'] = DateHelper::formatDate(
                    $result['tsOn'], $dateFormat, $timeZone
                );
            }

            if ($result['tsOff'] ?? null) {
                $result['tsOff'] = DateHelper::formatDate(
                    $result['tsOff'], $dateFormat, $timeZone
                );
            }

            $results[] = $result;
        }

        return TranslateHelper::translateEntityArrayForExport(
            $results, $translator, array_merge($fields, ['distanceTotal', 'durationTotal']), 'vehicle_io'
        );
    }
}