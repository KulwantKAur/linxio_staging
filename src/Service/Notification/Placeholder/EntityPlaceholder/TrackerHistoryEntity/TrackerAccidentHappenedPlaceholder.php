<?php

namespace App\Service\Notification\Placeholder\EntityPlaceholder\TrackerHistoryEntity;

use App\Service\Notification\Placeholder\AbstractEntityPlaceholder;

class TrackerAccidentHappenedPlaceholder extends AbstractEntityPlaceholder
{
    public function getInternalMappedPlaceholder(): array
    {
        return [
            'from_company' => 'fromCompany',
            'device' => 'device',
            'reg_no_or_device' => 'regNoOrDevice',
            'vehicle' => 'regNo',
            'model' => 'model',
            'driver' => 'driver',
            'event_time' => 'tsTime',
            'reg_no_with_model' => 'regNoWithModel',
            'vehicle_url' => 'vehicleUrl',
            'driver_url' => 'driverUrl',
            'reg_no' => 'regNo',
        ];
    }
}
