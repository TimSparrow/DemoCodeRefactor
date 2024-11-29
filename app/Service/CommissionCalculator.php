<?php

namespace App\Service;

use App\Models\CountryData;

class CommissionCalculator implements CommissionInterface
{
    public function getTransactionCommission(float $amount, string $countryCode): float
    {
        return $amount * $this->getCommissionRate($countryCode);
    }

    private function getCommissionRate(string $country): float
    {
        return CountryData::isEu($country) ? self::COMMISSION_EU : self::COMMISSION_DEFAULT;
    }
}