<?php

namespace App\Service;

use App\Models\CountryData;

class CommissionCalculator implements CommissionInterface
{
    private const int DECIMAL_PLACES = 2;
    public function getTransactionCommission(float $amount, string $countryCode): float
    {
        $roundFactor = pow(10, self::DECIMAL_PLACES);
        return ceil($roundFactor * $amount * $this->getCommissionRate($countryCode)) / $roundFactor;
    }

    private function getCommissionRate(string $country): float
    {
        return CountryData::isEu($country) ? self::COMMISSION_EU : self::COMMISSION_DEFAULT;
    }
}