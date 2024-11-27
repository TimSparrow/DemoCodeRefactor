<?php

namespace App\Service;

interface BinValidationService
{
    public function getCountryByBinNumber(string $binNumber): string;
}