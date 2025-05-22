<?php

declare(strict_types = 1);

namespace App\Report\Core\Exception;

class UndefinedReportOutputException extends \Exception
{
    /**
     * UndefinedReportOutputException constructor.
     * @param string $message
     */
    public function __construct(string $message)
    {
        parent::__construct($message);
    }
}
