<?php

namespace App\Service\Payment\StripeFeeRegionHandler;

interface FeeRegionInterface
{
    public function getAmountByType(float $amount, string $type, ?string $cardCountry = 'au'): float;

    public function getFeeByType(float $amount, string $type, ?string $cardCountry = 'au'): float;
}