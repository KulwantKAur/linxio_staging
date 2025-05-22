<?php

namespace App\Service\Payment\StripeFeeRegionHandler;

class AuFeeRegion implements FeeRegionInterface
{
    private const DOMESTIC_CARD_FEE_PERCENT = 0.0175;
    private const DOMESTIC_CARD_FEE_TAX = 0.3;

    private const INTERNATIONAL_CARD_FEE_PERCENT = 0.029;
    private const INTERNATIONAL_CARD_FEE_TAX = 0.3;

    private const BECS_FEE_PERCENT = 0.01;
    private const BECS_FEE_TAX = 0.3;

    public function getAmountByType(float $amount, string $type, ?string $cardCountry = 'au'): float
    {
        return round(match ($type) {
            'card' => $this->getAmountByCardCountry($amount, $cardCountry),
            'au_becs_debit' => $this->getBecsTotalAmountWithFee($amount)
        }, 2);
    }

    public function getFeeByType(float $amount, string $type, ?string $cardCountry = 'au'): float
    {
        return round(match ($type) {
            'card' => $this->getFeeByCardCountry($amount, $cardCountry),
            'au_becs_debit' => $this->getBecsFee($amount)
        }, 2);
    }

    public function getFeeByCardCountry(float $amount, ?string $cardCountry): float
    {
        return match (strtolower($cardCountry ?? '')) {
            'au' => $this->getDomesticFee($amount),
            default => $this->getInternationalFee($amount)
        };
    }

    public function getAmountByCardCountry(float $amount, ?string $cardCountry): float
    {
        return match (strtolower($cardCountry ?? '')) {
            'au' => $this->getDomesticTotalAmount($amount),
            default => $this->getInternationalTotalAmount($amount)
        };
    }

    public function getDomesticTotalAmount(float $amount): float
    {
        return ($amount + self::DOMESTIC_CARD_FEE_TAX) / (1 - self::DOMESTIC_CARD_FEE_PERCENT);
    }

    public function getDomesticFee(float $amount): float
    {
        return $this->getDomesticTotalAmount($amount) - $amount;
    }

    public function getInternationalTotalAmount(float $amount): float
    {
        return ($amount + self::INTERNATIONAL_CARD_FEE_TAX) / (1 - self::INTERNATIONAL_CARD_FEE_PERCENT);
    }

    public function getInternationalFee(float $amount): float
    {
        return $this->getInternationalTotalAmount($amount) - $amount;
    }

    public function getBecsTotalAmount(float $amount)
    {
        return ($amount + self::BECS_FEE_TAX) / (1 - self::BECS_FEE_PERCENT);
    }

    public function getBecsFee(float $amount): float
    {
        return min($this->getBecsTotalAmount($amount) - $amount, 3.5);
    }

    public function getBecsTotalAmountWithFee($amount)
    {
        return $amount + $this->getBecsFee($amount);
    }
}