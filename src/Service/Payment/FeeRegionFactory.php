<?php

namespace App\Service\Payment;

use App\Service\Payment\StripeFeeRegionHandler\AuFeeRegion;
use App\Service\Payment\StripeFeeRegionHandler\FeeRegionInterface;

class FeeRegionFactory
{
    public static function getFeeRegionHandler(string $region = 'au'): FeeRegionInterface
    {
        return match ($region) {
            default => new AuFeeRegion()
        };
    }
}