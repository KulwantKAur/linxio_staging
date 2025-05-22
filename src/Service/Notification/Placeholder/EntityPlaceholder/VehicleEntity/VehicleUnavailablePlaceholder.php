<?php

namespace App\Service\Notification\Placeholder\EntityPlaceholder\VehicleEntity;

use App\Service\Notification\Placeholder\AbstractEntityPlaceholder;

class VehicleUnavailablePlaceholder extends AbstractEntityPlaceholder
{
    public function getInternalMappedPlaceholder(): array
    {
        return [
            'from_company' => 'fromCompany',
            'reg_no' => 'regNo',
            'model' => 'model',
            'team' => 'team',
            'status' => 'status',
            'triggered_by' => 'updateBy',
            'event_time' => 'eventTime',
            'reg_no_with_model' => 'regNoWithModel',
            'driver' => 'driver',
            'vehicle_url' => 'vehicleUrl',
        ];
    }
}
