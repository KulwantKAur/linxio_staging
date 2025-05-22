<?php

namespace App\Report\Core\Formatter\Header;

interface HeaderFormatterInterface
{
    public function format($item): array;
}