<?php

interface ExchangeRateInterface
{
    public function getExchangeRate(string $currencyCode): float;
}