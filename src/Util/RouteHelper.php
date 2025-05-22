<?php


namespace App\Util;


class RouteHelper
{
    public static function calcDrivingTimeByRoutes(array $routes, string $dateFrom, string $dateTo)
    {
        $dateFrom = strtotime($dateFrom);
        $dateTo = strtotime($dateTo);
        $drivingTime = 0;

        foreach ($routes as $route) {
            if ($route['started_at'] < $dateFrom) {
                $route['started_at'] = $dateFrom;
            }
            if ($route['finished_at'] > $dateTo) {
                $route['finished_at'] = $dateTo;
            }
            $drivingTime += $route['finished_at'] - $route['started_at'];
        }

        return $drivingTime;
    }

    public static function calcDrivingTimeByRoutesVehicleArray(array $routes, string $dateFrom, string $dateTo)
    {
        $dateFrom = strtotime($dateFrom);
        $dateTo = strtotime($dateTo);

        $vehicles = [];
        $data = [];
        foreach ($routes as $route) {
            $vehicles[$route['vehicle_id']][] = $route;
        }
        foreach ($vehicles as $vehicle => $vehicleData) {
            $data[$vehicle] = 0;
            foreach ($vehicleData as $route) {
                if ($route['started_at'] < $dateFrom) {
                    $route['started_at'] = $dateFrom;
                }
                if ($route['finished_at'] > $dateTo) {
                    $route['finished_at'] = $dateTo;
                }
                $data[$vehicle] += $route['finished_at'] - $route['started_at'];
            }
        }

        return $data;
    }
}