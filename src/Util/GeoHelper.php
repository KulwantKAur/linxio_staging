<?php

namespace App\Util;


use App\Entity\Device;
use App\Entity\Tracker\TrackerHistory;
use Carbon\Carbon;

class GeoHelper
{
    public const DEFAULT_RANGE = 1000; //metres

    /**
     * Calculates the great-circle distance between two points, with
     * the Vincenty formula.
     * @param float $latFrom Latitude of start point in [deg decimal]
     * @param float $lngFrom Longitude of start point in [deg decimal]
     * @param float $latTo Latitude of target point in [deg decimal]
     * @param float $lngTo Longitude of target point in [deg decimal]
     * @param float $earthRadius Mean earth radius in [m]
     * @return float Distance between points in [m] (same as earthRadius)
     */
    public static function distanceBetweenTwoCoordinates(
        float $latFrom,
        float $lngFrom,
        float $latTo,
        float $lngTo,
        float $earthRadius = 6371000
    ) {
        // convert from degrees to radians
        $latFrom = deg2rad($latFrom);
        $lonFrom = deg2rad($lngFrom);
        $latTo = deg2rad($latTo);
        $lonTo = deg2rad($lngTo);

        $lonDelta = $lonTo - $lonFrom;
        $a = pow(cos($latTo) * sin($lonDelta), 2) +
            pow(cos($latFrom) * sin($latTo) - sin($latFrom) * cos($latTo) * cos($lonDelta), 2);
        $b = sin($latFrom) * sin($latTo) + cos($latFrom) * cos($latTo) * cos($lonDelta);

        $angle = atan2(sqrt($a), $b);

        return round($angle * $earthRadius);
    }

    public static function distanceBetweenTwoCoordinates2(array $a, array $b): float
    {
        list($lat1, $lon1) = $a;
        list($lat2, $lon2) = $b;

        $theta = $lon1 - $lon2;
        $dist = sin(deg2rad($lat1)) * sin(deg2rad($lat2)) + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * cos(deg2rad($theta));
        $dist = acos($dist);
        $dist = rad2deg($dist);

        return $dist * 60 * 1.1515;
    }

    /**
     * @param float $latFrom
     * @param float $lngFrom
     * @param float $latTo
     * @param float $lngTo
     * @param float $range
     * @return bool
     */
    public static function checkPointsInRange(
        float $latFrom,
        float $lngFrom,
        float $latTo,
        float $lngTo,
        float $range = self::DEFAULT_RANGE
    ): bool {
        $distanceBetweenPoints = self::distanceBetweenTwoCoordinates($latFrom, $lngFrom, $latTo, $lngTo);

        return $range >= $distanceBetweenPoints;
    }

    /**
     * @param array $coordinates
     * @return array
     * @throws \Exception
     */
    public static function convertCoordinatesForResponse(array $coordinates): array
    {
        if (isset($coordinates['ts'])) {
            $coordinates['ts'] = DateHelper::formatDate($coordinates['ts']);
        }

        if (array_key_exists('lat', $coordinates) && array_key_exists('lng', $coordinates)) {
            $coordinates['nullable'] = (!(double)$coordinates['lat'] && !(double)$coordinates['lng']);
        }

        return $coordinates;
    }

    /**
     * @param array $items
     * @return int
     */
    public static function calcDrivingTimeAccordingToDeviceStatus(array $items)
    {
        $totalDrivingSeconds = 0;

        foreach ($items as $key => $item) {
            $itemStatus = TrackerHistory::getDeviceStatusByIgnitionAndMovement(
                $item['ignition'],
                $item['movement']
            );
            $nextItem = $items[$key + 1] ?? null;

            if ($nextItem && $itemStatus == Device::STATUS_DRIVING) {
                $totalDrivingSeconds += ($nextItem['ts'] - $item['ts']);
            }
        }

        return $totalDrivingSeconds;
    }

    public static function calcDrivingTimeAccordingToDeviceStatusForVehicleArray(array $items)
    {
        $vehicles = [];
        $data = [];
        foreach ($items as $item) {
            $vehicles[$item['vehicle_id']][] = $item;
        }

        foreach ($vehicles as $vehicle => $vehicleData) {
            $data[$vehicle] = 0;
            foreach ($vehicleData as $key => $item) {
                $itemStatus = TrackerHistory::getDeviceStatusByIgnitionAndMovement(
                    $item['ignition'],
                    $item['movement']
                );
                $nextItem = $vehicleData[$key + 1] ?? null;

                if ($nextItem && $itemStatus == Device::STATUS_DRIVING) {
                    $data[$vehicle] += ($nextItem['ts'] - $item['ts']);
                }
            }
        }

        return $data;
    }

    /**
     * @param mixed $lat
     * @param mixed $lng
     * @return bool
     */
    public static function hasCoordinatesWithCorrectValue($lat, $lng): bool
    {
        return (!is_null($lat) && !is_null($lng) && (double)$lat !== 0.0 && (double)$lng !== 0.0);
    }

    public static function coordinatesOfCircle($center, $radius, $numberOfSegments = 360)
    {
        $n = $numberOfSegments;
        $flatCoordinates = [];
        for ($i = 0; $i < $n; $i++) {
            $bearing = 2 * M_PI * $i / $n;
            $flatCoordinates[] = self::offset($center, $radius, $bearing);
        }
        $flatCoordinates[] = $flatCoordinates[0];

        return $flatCoordinates;
    }

    public static function offset($c1, $distance, $bearing)
    {
        $lat1 = deg2rad($c1[0]);
        $lon1 = deg2rad($c1[1]);
        $dByR = $distance / 6378137; // convert dist to angular distance in radians

        $lat = asin(
            sin($lat1) * cos($dByR) +
            cos($lat1) * sin($dByR) * cos($bearing)
        );
        $lon = $lon1 + atan2(
                sin($bearing) * sin($dByR) * cos($lat1),
                cos($dByR) - sin($lat1) * sin($lat)
            );
        $lon = fmod(
                $lon + 3 * M_PI,
                2 * M_PI
            ) - M_PI;
        return ['lng' => rad2deg($lon), 'lat' => rad2deg($lat)];
    }
}