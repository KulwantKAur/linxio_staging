<?php

declare(strict_types = 1);

namespace App\Report\Core\Exception;

class UndefinedReportException extends \Exception
{
    /**
     * UndefinedReportException constructor.
     * @param string $message
     */
    public function __construct(string $message)
    {
        parent::__construct($message);
    }
}
