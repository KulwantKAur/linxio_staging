<?php

namespace App\Exceptions\Route;

class AssignRouteDriverException extends \Exception
{
    private $context = [];

    public function setContext(array $data)
    {
        $this->context = $data;

        return $this;
    }

    public function getContext(): array
    {
        return $this->context;
    }
}