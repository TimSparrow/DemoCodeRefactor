<?php

namespace App\Models;

class CountryData
{
    private const array EU_COUNTRIES = [
        'AT','BE','BG','CY','CZ','DE','DK','EE','ES','FI','FR','GR','HR','HU','IE','IT','LT','LU','LV','MT','NL','PO','PT','RO','SE','SI','SK'
    ];

    public static function isEu(string $countryCode): bool
    {
        return in_array(strtoupper($countryCode), self::EU_COUNTRIES);
    }
}