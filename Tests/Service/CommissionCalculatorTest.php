<?php

namespace Test\Service;

use App\Service\CommissionCalculator;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversClass(CommissionCalculator::class)]
class CommissionCalculatorTest extends TestCase
{
    public static function commissionDataProvider(): array
    {
        return [
            [0.01, 1, 'DE'],
            [0.04, 2, 'CN'],
            [1, 100, 'FR'],
            [4, 200, 'IL'],
            [6, 300, 'MX'],
        ];
    }


    #[DataProvider('commissionDataProvider')]
    public function testGetCommission($expected, $amount, $country)
    {
        $commissionCalculator = new CommissionCalculator();
        $actual = $commissionCalculator->getTransactionCommission($amount, $country);
        $this->assertEquals($expected, $actual);
    }
}
