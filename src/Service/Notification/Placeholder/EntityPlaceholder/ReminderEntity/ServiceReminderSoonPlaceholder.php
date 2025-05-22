<?php

namespace App\Service\Notification\Placeholder\EntityPlaceholder\ReminderEntity;

use App\Service\Notification\Placeholder\AbstractEntityPlaceholder;

class ServiceReminderSoonPlaceholder extends AbstractEntityPlaceholder
{
    public function getInternalMappedPlaceholder(): array
    {
        return [
            'from_company' => 'fromCompany',
            'reg_no' => 'regNo',
            'model' => 'model',
            'team' => 'team',
            'status' => 'status',
            'title' => 'title',
            'expiration_date' => 'expirationDate',
            'expiration_parameter' => 'expireParameter',
            'event_time' => 'eventTime',
            'entity' => 'entity',
            'data_url' => 'dataUrl',
        ];
    }
}
