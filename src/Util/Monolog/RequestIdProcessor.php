<?php

namespace App\Util\Monolog;

use Monolog\LogRecord;
use Monolog\Processor\UidProcessor;

class RequestIdProcessor extends UidProcessor
{
    public function __invoke(LogRecord $record): LogRecord
    {
        $record->extra['req']['id'] = $_SERVER['HTTP_X_REQUEST_ID'] ?? $this->getUid();

        return $record;
    }
}
