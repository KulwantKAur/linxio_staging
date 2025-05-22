<?php

namespace App\Service\Device\Consumer;

class MessageHelper
{
    public static function getTHFields(): array
    {
        return [
            'id',
            'tsISO8601',
            'createdAt',
            'movement',
            'ignition',
            'driverId',
            'mileageFromTracker',
            'speed',
            'lat',
            'lng',
            'odometer',
            'batteryVoltagePercentage',
            'deviceId',
            'lastCoordinates',
            'address',
            'angle',
            'lastDataReceived',
            'temperatureLevel',
            'mileage',
            'engineHours',
            'batteryVoltage',
            'externalVoltage',
            'standsIgnition',
            'iButton',
            'engineOnTime',
        ];
    }
}
