<?php

namespace Models;
use App\Models\CountryData;

use PHPUnit\Framework\TestCase;

/**
 * @covers \App\Models\CountryData
 */
class CountryDataTest extends TestCase
{
    private function getEuDataStream(): array
    {
       return [

           ['DE'],
           ['at'],
           ['fR']
       ];
    }

    private function getNonEuDataStream(): array
    {
        return [
            ['UA'],
            ['CA'],
            ['GB'],
        ];
    }

    /**
     * @dataProvider getEuDataStream
     */
    public function testEuDataStream(string $countryCode): void
    {
        $this->assertTrue(CountryData::isEu($countryCode));
    }

    public function testNonEuDataStream(string $countryCode): void
    {
        $this->assertFalse(CountryData::isEu($countryCode));
    }
}