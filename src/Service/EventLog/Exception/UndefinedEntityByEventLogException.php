<?php

declare(strict_types = 1);

namespace App\Service\EventLog\Exception;

class UndefinedEntityByEventLogException extends \Exception
{
    /**
     * UndefinedEntityByEventLogException constructor.
     * @param string $message
     */
    public function __construct(string $message)
    {
        parent::__construct($message);
    }
}
