<?php

namespace App\Service;

interface CommissionInterface
{
    public const float COMMISSION_EU = 0.01;
    public const float COMMISSION_DEFAULT = 0.02;
    public function getTransactionCommission(float $amount, string $countryCode): float;
}