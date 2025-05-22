<?php

namespace App\Service\MapService;


interface MapServiceInterface
{
    /**
     * @param $lat
     * @param $lng
     * @return string|null
     */
    public function getLocationByCoordinates($lat, $lng): ?string;

    public function getCoordinatesByLocation(string $location): ?array;
}