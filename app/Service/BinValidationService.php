<?php

namespace Service;

interface BinValidationService
{
    public function getCountryByBinNumber(string $binNumber): string;
}