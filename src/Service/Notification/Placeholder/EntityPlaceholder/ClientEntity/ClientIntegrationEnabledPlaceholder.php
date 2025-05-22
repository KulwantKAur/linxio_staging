<?php

namespace App\Service\Notification\Placeholder\EntityPlaceholder\ClientEntity;

use App\Service\Notification\Placeholder\AbstractEntityPlaceholder;

class ClientIntegrationEnabledPlaceholder extends AbstractEntityPlaceholder
{
    public function getInternalMappedPlaceholder(): array
    {
        return [
            'from_company' => 'fromCompany',
            'client_name' => 'clientName',
            'team' => 'team',
            'triggered_by' => 'updateBy',
            'event_time' => 'updateTime',
            'data_url' => 'dataUrl',
            'integration_name' => 'integrationName'
        ];
    }
}
