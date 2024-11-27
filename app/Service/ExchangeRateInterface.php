<?php

namespace App\Service;

interface ExchangeRateInterface
{
    public function getExchangeRate(string $currencyCode): float;
}