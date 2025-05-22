<?php

declare(strict_types=1);

namespace App\Service\Notification\Placeholder\EntityPlaceholder\TeamEntity;

use App\Service\Notification\Placeholder\AbstractEntityPlaceholder;

/**
 *
 */
class StripeIntegrationErrorPlaceholder extends AbstractEntityPlaceholder
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
