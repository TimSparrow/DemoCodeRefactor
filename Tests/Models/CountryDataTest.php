<?php


namespace Models;

use App\Models\CountryData;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;




#[CoversClass(CountryData::class)]
class CountryDataTest extends TestCase
{
    public static  function getEuDataStream(): array
    {
       return [

           ['DE'],
           ['at'],
           ['fR']
       ];
    }

    public static function getNonEuDataStream(): array
    {
        return [
            ['UA'],
            ['CA'],
            ['GB'],
        ];
    }

    #[DataProvider('getEuDataStream')]
    public function testEuDataStream(string $countryCode): void
    {
        $this->assertTrue(CountryData::isEu($countryCode));
    }

    #[DataProvider('getNonEuDataStream')]
    public function testNonEuDataStream(string $countryCode): void
    {
        $this->assertFalse(CountryData::isEu($countryCode));
    }
}