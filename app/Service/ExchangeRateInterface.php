<?php

namespace App\Service;

interface ExchangeRateInterface
{

    public function getExchangeRate(string $currencyCode): float;

    public function getAmountConverted(float $amount, string $currencyCode): float;
}