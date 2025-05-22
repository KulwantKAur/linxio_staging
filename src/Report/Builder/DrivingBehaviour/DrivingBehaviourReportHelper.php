<?php

namespace App\Report\Builder\DrivingBehaviour;

use App\Entity\DrivingBehavior;
use App\Entity\User;
use App\Entity\Vehicle;
use App\Service\BaseService;
use App\Util\TranslateHelper;
use Symfony\Contracts\Translation\TranslatorInterface;


class DrivingBehaviourReportHelper
{
    public static function convertDatesToUTC(array $params): array
    {
        if (isset($params['startDate'])) {
            $params['startDate'] = BaseService::parseDateToUTC($params['startDate']);
        }
        if (isset($params['endDate'])) {
            $params['endDate'] = BaseService::parseDateToUTC($params['endDate']);
        }

        return $params;
    }

    public static function prepareVehicleSummaryElasticaParams(array $requestParams)
    {
        $params = [];

        foreach (['regNo', 'depot', 'groups', 'showArchived', 'driverIdForHistoryVehicles', 'vehicleIds'] as $propName) {
            if (!empty($requestParams[$propName])) {
                $params[$propName] = $requestParams[$propName];
            }
        }

        return $params;
    }

    public static function prepareVehiclesSummaryReportParams($requestParams)
    {
        $params = [];
        $params = array_merge($params, self::prepareDatePeriod($requestParams));
        $vehicleIds = array_flip(array_keys($requestParams['excSpeedMap']));
        $vehicleValuesAsIds = array_values(array_keys($vehicleIds));
        $params['vehicleIds'] = array_combine(array_keys($vehicleIds), $vehicleValuesAsIds);
        $sortFields = isset($requestParams['sort']) && $requestParams['sort'] ? explode(
            ',',
            $requestParams['sort']
        ) : [];
        foreach ($sortFields as $sortItem) {
            if (!isset($params['order'])) {
                $params['order'] = [];
            }

            $prefix = strpos($sortItem, '-') === false ? 'ASC' : 'DESC';
            $params['order'][] = [ltrim($sortItem, ' -'), $prefix];
        }
        if (!isset($params['order'])) {
            $params['order'] = [['vehicleId', 'ASC',]];
        }

        return $params;
    }

    public static function buildExcessiveSpeedMap($vehicles)
    {
        $map = [];

        foreach ($vehicles as $vehicle) {
            /** @var Vehicle $vehicle */
            $map[$vehicle->getId()] = $vehicle->getId();
        }

        return $map;
    }

    /**
     * @param array $item
     * @return array
     * @throws \Exception
     */
    public static function formatCommonRows(array $item): array
    {
        return [
            'harshAccelerationCount' => self::formatEventCount($item['harshaccelerationcount']),
            'harshBrakingCount' => self::formatEventCount($item['harshbrakingcount']),
            'harshCorneringCount' => self::formatEventCount($item['harshcorneringcount']),
            'harshAccelerationScore' => self::roundFloatScore($item['harshaccelerationscore']),
            'harshBrakingScore' => self::roundFloatScore($item['harshbrakingscore']),
            'harshCorneringScore' => self::roundFloatScore($item['harshcorneringscore']),
            'totalDistance' => $item['totalDistance'],
            'idlingCount' => self::formatEventCount($item['idlingcount']),
            'excessiveIdling' => self::roundFloatScore($item['excessiveidling']),
            'ecoSpeedEventCount' => self::formatEventCount($item['ecospeedeventcount']),
            'ecoSpeedTotalDistance' => $item['ecospeedtotaldistance'],
            'speeding' => self::roundFloatScore($item['speeding']),
            'ecoSpeed' => self::roundFloatScore($item['ecospeed']),
            'idlingTotalTime' => $item['idlingtotaltime'],
            'drivingTotalTime' => $item['drivingTotalTime'],
            'totalAvgSpeed' => $item['totalAvgSpeed'],
            'totalScore' => self::roundFloatScore(
                self::calcTotalScore(
                    $item,
                    [
                        'harshaccelerationscore',
                        'harshbrakingscore',
                        'harshcorneringscore',
                        'excessiveidling',
                        'ecospeed',
                    ]
                )
            ),
        ];
    }

    public static function roundFloatScore($value)
    {
        return $value && ($value != 0) ? round(floatval($value), 2) : 0;
    }

    public static function formatEventCount($value): int
    {
        return $value ? $value : 0;
    }

    public static function calcTotalScore(array $item, array $keys): float
    {
        $values = [];

        foreach ($keys as $key) {
            if (array_key_exists($key, $item) && !is_null($item[$key])) {
                $values[] = $item[$key];
            } else {
                $values[] = 100;
            }
        }

        return count($values) ? array_sum($values) / count($values) : 0;
    }

    public static function mapOrder(array $order): array
    {
        switch ($order[0][0]) {
            case 'harshAcceleration':
                $key = 'harshAccelerationScore';
                break;
            case 'harshBraking':
                $key = 'harshBrakingScore';
                break;
            case 'harshCornering':
                $key = 'harshCorneringScore';
                break;
            case 'ecoSpeed':
                $key = 'ecoSpeedScore';
                break;
            default:
                $key = $order[0][0];
                break;
        }

        $order[0][0] = $key;

        return $order;
    }

    /**
     * @param null|array $data
     * @param $key
     * @param string $direction
     * @return array|null
     */
    public static function sortMultidimensionalArray(?array $data, $key, $direction = 'DESC'): ?array
    {
        if ($data && array_key_exists($key, $data[0])) {
            usort($data, function ($a, $b) use ($key, $direction) {
                return $direction == 'DESC' ? $b[$key] <=> $a[$key] : $a[$key] <=> $b[$key];
            });
        }

        return $data;
    }

    public static function getMapBy($list, string $field): array
    {
        $map = [];
        foreach ($list as $item) {
            if (is_object($item)) {
                if (method_exists($item, sprintf('get%s', ucfirst($field)))) {
                    $getter = sprintf('get%s', ucfirst($field));
                    $map[$item->$getter()] = $item;
                } elseif (property_exists($item, $field)) {
                    $map[$item->$field] = $item;
                } else {
                    $map[] = $item;
                }
            } elseif (is_array($item) && isset($item[$field])) {
                $map[$item[$field]] = $item;
            } else {
                $map[] = $item;
            }
        }

        return $map;
    }

    public static function prepareVehicleExportData($data, $params, TranslatorInterface $translator)
    {
        $results = [];
        foreach ($data as $key => $item) {
            $results[] = DrivingBehavior::toExportVehicleSummary($item, $params['fields']);
        }

        return TranslateHelper::translateEntityArrayForExport($results, $translator, [], DrivingBehavior::class);
    }

    public static function prepareDriverExportData($data, $params, TranslatorInterface $translator)
    {
        $results = [];
        foreach ($data as $key => $item) {
            $results[] = DrivingBehavior::toExportDriverSummary($item, $params['fields']);
        }

        return TranslateHelper::translateEntityArrayForExport($results, $translator, [], DrivingBehavior::class);
    }

    public static function prepareDriverSummaryElasticaParams(array $requestParams)
    {
        $params = [];

        foreach (['email', 'name', 'surname', 'driver', 'driverId', 'driverIdForHistoryVehicles'] as $propName) {
            if (!empty($requestParams[$propName])) {
                $params[$propName] = $requestParams[$propName];
            }
        }

        return $params;
    }


    public static function prepareDriversSummaryReportParams($requestParams)
    {
        $params = [];
        $params = array_merge($params, self::prepareDatePeriod($requestParams));
        $driverIds = isset($requestParams['excSpeedMap']) ? array_flip(array_keys($requestParams['excSpeedMap'])) : [];
        $driverValuesAsIds = array_values(array_keys($driverIds));
        $params['driverIds'] = array_combine(array_keys($driverIds), $driverValuesAsIds);
        $sortFields = isset($requestParams['sort']) && $requestParams['sort'] ? explode(
            ',',
            $requestParams['sort']
        ) : [];
        foreach ($sortFields as $sortItem) {
            if (!isset($params['order'])) {
                $params['order'] = [];
            }

            $prefix = strpos($sortItem, '-') === false ? 'ASC' : 'DESC';
            $params['order'][] = [ltrim($sortItem, ' -'), $prefix];
        }
        if (!isset($params['order'])) {
            $params['order'] = [['driverId', 'ASC',]];
        }

        return $params;
    }


    /**
     * @param array $requestParams
     * @return array
     * @throws \Exception
     */
    public static function prepareDatePeriod(array $requestParams): array
    {
        $params = $requestParams;
        $getFirstMonthDay = static function (\DateTime $date): \DateTime {
            $newDate = clone $date;
            return $newDate
                ->setDate(
                    $newDate->format('Y'),
                    $newDate->format('m'),
                    1
                )
                ->setTime(0, 0);
        };
        $getLastMonthDay = static function (\DateTime $date) {
            $newDate = clone $date;
            return $newDate
                ->setDate(
                    $newDate->format('Y'),
                    $newDate->format('m'),
                    1
                )
                ->setTime(23, 59, 59)
                ->modify('+1 month')
                ->modify('-1 day');
        };

        if (!empty($requestParams['startDate'])) {
            $params['startDate'] = new \DateTime($requestParams['startDate']);
        } elseif (!empty($requestParams['endDate'])) {
            $params['startDate'] = $getFirstMonthDay(new \DateTime($requestParams['startDate']));
        } else {
            $params['startDate'] = $getFirstMonthDay(new \DateTime());
        }

        if (!empty($requestParams['endDate'])) {
            $params['endDate'] = new \DateTime($requestParams['endDate']);
        } else {
            $params['endDate'] = $getLastMonthDay($params['startDate']);
        }

        $params['startDate'] = $params['startDate']->format('Y-m-d H:i:s');
        $params['endDate'] = $params['endDate']->format('Y-m-d H:i:s');

        return $params;
    }

    public static function buildExcessiveSpeedMapForDrivers($drivers)
    {
        $map = [];

        foreach ($drivers as $driver) {
            /** @var User $driver */
            $map[$driver->getId()] = $driver->getId();
        }

        return $map;
    }
}