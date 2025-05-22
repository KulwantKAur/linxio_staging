<?php

namespace App\Service\Notification\Placeholder\EntityPlaceholder\TrackerHistoryIOEntity;

use App\Service\Notification\Placeholder\AbstractEntityPlaceholder;

class TrackerHistoryIOPlaceholder extends AbstractEntityPlaceholder
{
    public function getInternalMappedPlaceholder(): array
    {
        return [
            'from_company' => 'fromCompany',
            'reg_no' => 'regNo',
            'model' => 'model',
            'sensor_status' => 'sensorStatus',
            'sensor_io_type' => 'sensorIOType',
            'device_imei' => 'deviceImei',
            'device_or_vehicle' => 'deviceOrVehicle',
            'regno_or_imei' => 'regNoWithModelOrDevice',
            'triggered_by' => 'driver',
            'event_time' => 'eventTime',
            'vehicle_url' => 'vehicleUrl',
        ];
    }
}
