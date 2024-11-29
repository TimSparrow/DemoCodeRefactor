<?php

namespace Test\Service;

use App\Models\CountryData;
use App\Service\BinValidationService;
use App\Service\CommissionInterface;
use App\Service\CommissionReportProcessor;
use App\Service\ExchangeRateInterface;
use App\Service\ReaderInterface;
use Faker\Factory;
use Faker\Generator;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(CommissionReportProcessor::class)]
class CommissionReportProcessorTest extends MockeryTestCase
{

    private const int MAX_EMTRIES = 10;

    private const BASE_CURRENCY = CommissionReportProcessor::BASE_CURRENCY;

    private const BASE_CURRENCY_PROB = 0.4;


    private ExchangeRateInterface|MockInterface $exchangeRatesService;
    private BinValidationService|MockInterface $binValidator;

    private ReaderInterface|MockInterface $reader;

    private CommissionInterface|MockInterface $commissionCalculator;

    private Generator $faker;

    public function setUp(): void
    {
        parent::setUp();

        $this->binValidator = Mockery::mock(BinValidationService::class);
        $this->exchangeRatesService = Mockery::mock(ExchangeRateInterface::class);
        $this->reader = Mockery::mock(ReaderInterface::class);
        $this->commissionCalculator = Mockery::mock(CommissionInterface::class);
        $this->faker = Factory::create();
    }

    private function getCalculator(): CommissionReportProcessor
    {

        return new CommissionReportProcessor(
            $this->binValidator,
            $this->exchangeRatesService,
            $this->commissionCalculator,
            $this->reader
        );
    }

    public function testCreateReport()
    {
        $calculator = $this->getCalculator();

        // make random number of tests, not exceeding the limit
        $entriesCount = $this->faker->numberBetween(1, self::MAX_EMTRIES);

        // as from the transaction report
        $testData = $this->generateSourceData($entriesCount);

        // rates per currency
        $rates = $this->generateRates($testData);

        // totally random countries per fake BIN
        $countries = $this->generateCountries($entriesCount);

        // init the reader and yield the test data to the processor
        $this->reader->shouldReceive('eachLine')->andYield(...
            array_map(
            static function(array $entry): string{
                return json_encode($entry);
            }, $testData));


        // mock return values, substituting generated data
        for($i = 0; $i < count($testData); $i++) {
            $record = $testData[$i];
            $currency = $record['currency'];
            $country = $countries[$i];
            $convertedAmount = $currency === self::BASE_CURRENCY ? $record['amount'] : $record['amount'] / $rates[$currency];
            $commissionRate = (CountryData::isEu($country) ? CommissionInterface::COMMISSION_EU : CommissionInterface::COMMISSION_DEFAULT);
            $this->binValidator->shouldReceive('getCountryByBinNumber')
                ->with($record['bin'])->andReturn($country);
            $this->exchangeRatesService->shouldReceive('getAmountConverted')
                ->with((float)$record['amount'], $currency)
                ->andReturn($convertedAmount);

            $this->commissionCalculator->shouldReceive('getTransactionCommission')
                ->with($convertedAmount, $country)
                ->andReturn($convertedAmount * $commissionRate);
        }
        $report = $calculator->createReport();

        // now check the return data is correct
        $returnedData = $report->getTransactions();
        $this->assertEquals(count($returnedData), $entriesCount);

        for($i = 0; $i < count($returnedData); $i++) {
            $transactionCurrency = $testData[$i]['currency'];
            $commissionRate = CountryData::isEu($countries[$i]) ?
                CommissionInterface::COMMISSION_EU :
                CommissionInterface::COMMISSION_DEFAULT;
            if ($transactionCurrency === self::BASE_CURRENCY) {
                $this->assertEquals($returnedData[$i], $testData[$i]['amount'] * $commissionRate);
            } else {
                $this->assertEquals($returnedData[$i], $testData[$i]['amount'] / $rates[$transactionCurrency] * $commissionRate);
            }
        }
    }

    private function generateSourceData(int $entriesCount): array
    {
        $result = [];

        for ($i = 0; $i < $entriesCount; $i++) {
            $result[] = $this->createFakeEntry();
        }
        return $result;
    }


    /**
     * @return array
     */
    private function createFakeEntry(): array
    {
        return [
            'bin' => $this->createRandomBin(),
            'amount' => $this->faker->randomFloat(),
            'currency' => $this->getCurrencyCode(),
        ];

    }

    /**
     * Make sure we have enough base currency transactions
     */
    private function getCurrencyCode(): string
    {
        $prob = $this->faker->randomFloat(0, 1);
        return ($prob <= self::BASE_CURRENCY_PROB) ? self::BASE_CURRENCY : $this->faker->currencyCode();
    }

    private function createRandomBin(): string
    {
        return substr($this->faker->creditCardNumber, 0, 6);
    }

    /**
     * Generate fake exchange rates
     *
     * @param array $testData
     * @return array
     */
    private function generateRates(array $testData): array
    {
        $rates = [];
        foreach ($testData as $entry) {
            if (!array_key_exists($entry['currency'], $rates)) {
                // exchange rate must not be zero
                $rates[$entry['currency']] = $this->faker->randomFloat(0.01, 100);
            }
        }

        return $rates;
    }

    private function generateCountries(int $entriesCount): array
    {
        $countries = [];
        for ($i = 0; $i < $entriesCount; $i++) {
            $countries[] = $this->faker->countryCode();
        }
        return $countries;
    }

    public function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}
