<?php

namespace App\Service\Notification\Placeholder\EntityPlaceholder\DeviceEntity;

use App\Service\Notification\Placeholder\AbstractEntityPlaceholder;

class DeviceContractExpiredPlaceholder extends AbstractEntityPlaceholder
{
    public function getInternalMappedPlaceholder(): array
    {
        return [
            'imei_array' => 'imeiArray',
            'count' => 'count',
        ];
    }
}
