<?php

declare(strict_types = 1);

namespace App\Service\Notification\Placeholder\Exception;

class UndefinedPlaceholderByEventException extends \Exception
{
    public function __construct(string $message)
    {
        parent::__construct($message);
    }
}
