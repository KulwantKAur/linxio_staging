<?php

namespace App\Service\Notification\Placeholder\EntityPlaceholder\TeamEntity;

use App\Service\Notification\Placeholder\AbstractEntityPlaceholder;

class XeroIntegrationErrorPlaceholder extends AbstractEntityPlaceholder
{
    public function getInternalMappedPlaceholder(): array
    {
        return [
            'from_company' => 'fromCompany',
            'team' => 'team',
            'triggered_by' => 'triggeredBy',
            'error_details' => 'errorDetails',
            'event_time' => 'createdTime',
            'comment' => 'comment',
        ];
    }
}
