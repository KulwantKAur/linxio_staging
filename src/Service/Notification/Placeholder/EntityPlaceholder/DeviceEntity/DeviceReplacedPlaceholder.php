<?php

namespace App\Service\Notification\Placeholder\EntityPlaceholder\DeviceEntity;

use App\Service\Notification\Placeholder\AbstractEntityPlaceholder;

class DeviceReplacedPlaceholder extends AbstractEntityPlaceholder
{
    public function getInternalMappedPlaceholder(): array
    {
        return [
            'from_company' => 'fromCompany',
            'device_imei' => 'deviceImei',
            'status' => 'status',
            'event_time' => 'updateTime',
            'triggered_by' => 'updateBy',
            'data_url' => 'dataUrl',
        ];
    }
}
