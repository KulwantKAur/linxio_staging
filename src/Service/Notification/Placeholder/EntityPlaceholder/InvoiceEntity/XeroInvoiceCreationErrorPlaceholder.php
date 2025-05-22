<?php

declare(strict_types=1);

namespace App\Service\Notification\Placeholder\EntityPlaceholder\InvoiceEntity;

use App\Service\Notification\Placeholder\AbstractEntityPlaceholder;

/**
 *
 */
class XeroInvoiceCreationErrorPlaceholder extends AbstractEntityPlaceholder
{
    public function getInternalMappedPlaceholder(): array
    {
        return [
            'from_company' => 'fromCompany',
            'invoice' => 'invoiceId',
            'team' => 'team',
            'triggered_by' => 'triggeredBy',
            'error_details' => 'errorDetails',
            'event_time' => 'eventTime',
            'comment' => 'comment',
        ];
    }
}
