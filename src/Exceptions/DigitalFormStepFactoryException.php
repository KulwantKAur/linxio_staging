<?php

declare(strict_types = 1);

namespace App\Exceptions;

class DigitalFormStepFactoryException extends \Exception
{
    public function __construct(string $message)
    {
        parent::__construct($message);
    }
}