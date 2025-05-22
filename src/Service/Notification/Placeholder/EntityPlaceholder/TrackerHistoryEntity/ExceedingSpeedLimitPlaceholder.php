<?php

namespace App\Service\Notification\Placeholder\EntityPlaceholder\TrackerHistoryEntity;

use App\Service\Notification\Placeholder\AbstractEntityPlaceholder;

class ExceedingSpeedLimitPlaceholder extends AbstractEntityPlaceholder
{
    public function getInternalMappedPlaceholder(): array
    {
        return [
            'from_company' => 'fromCompany',
            'reg_no' => 'regNo',
            'default_label' => 'defaultLabel',
            'model' => 'model',
            'team' => 'team',
            'driver' => 'driver',
            'status' => 'status',
            'speed' => 'avgSpeed',
            'speed_limit' => 'speedLimit',
            'lat' => 'lat',
            'lng' => 'lng',
            'address' => 'address',
            'duration' => 'duration',
            'triggered_by' => 'triggeredBy',
            'event_time' => 'tsTime',
            'reg_no_with_model' => 'regNoWithModel',
            'vehicle_url' => 'vehicleUrl',
            'driver_url' => 'driverUrl',
        ];
    }
}
