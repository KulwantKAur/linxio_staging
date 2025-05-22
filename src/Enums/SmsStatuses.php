<?php

namespace App\Enums;

class SmsStatuses
{
    const PENDING = 'pending';
    const QUEUED = 'queued';
    const SENT = 'sent';
    const FAILED = 'failed';
    const DELIVERED = 'delivered';
    const UNDELIVERED = 'undelivered';
    const REJECTED = 'rejected';
}
