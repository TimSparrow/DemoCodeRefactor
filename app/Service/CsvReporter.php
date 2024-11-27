<?php


namespace App\Service;
use App\Models\Report;
use App\Service\BinValidationService;
use App\Models\CountryData;
use App\View\ReportView;
class CsvReporter
{
    private const string BASE_CURRENCY = "EUR";
    private const float COMMISSION_EU = 0.01;
    private const float COMMISSION_DEFAULT = 0.02;

    public function __construct(
        private readonly string                $csvFile,
        private readonly BinValidationService  $binValidator,
        private readonly ExchangeRateInterface $exchangeRates
    )
    {
        if (!file_exists($csvFile)) {
            throw new \Exception("File not found: $csvFile");
        }

    }

    private function importFeed(): \Generator
    {
        $rawData = file_get_contents($this->csvFile);
        $lines = explode(PHP_EOL, $rawData);
        foreach ($lines as $line) {
            yield json_decode($line);
        }
    }

    public function createReport(): Report
    {
        $report = new Report();

        foreach ($this->importFeed() as $record) {
            $country = $this->binValidator->getCountryByBinNumber($record['bin']);
            $amount = $this->convertAmount($record['amount'], $record['currency']);
            $commission = $amount * $this->getCommission($country);

            $report->add($commission);
        }

        return $report;
    }

    private function convertAmount(float $amount, string $currency): float
    {
        if ($currency === self::BASE_CURRENCY) {
            return $amount;
        }

        $rate = $this->exchangeRates->getExchangeRate($currency);
        return ($rate > 0) ? $amount / $rate : $amount;
    }

    private function getCommission(string $country): float
    {
        return CountryData::isEu($country) ? self::COMMISSION_EU : self::COMMISSION_DEFAULT;
    }
}