<?php


namespace App\Service;
use App\Models\Report;
use App\Service\BinValidationService;
use App\Models\CountryData;
use App\View\ReportView;
class CommissionCalculator
{
    public const string BASE_CURRENCY = "EUR";
    private const float COMMISSION_EU = 0.01;
    private const float COMMISSION_DEFAULT = 0.02;

    public function __construct(
        private readonly BinValidationService  $binValidator,
        private readonly ExchangeRateInterface $exchangeRates,
        private readonly ReaderInterface        $reader
    )
    {
    }


    public function createReport(): Report
    {
        $report = new Report();

        foreach ($this->reader->eachLine() as $line) {
            $record = json_decode($line, true);
            $country = $this->binValidator->getCountryByBinNumber($record['bin']);
            $amount = $this->exchangeRates->getAmountConverted((float)$record['amount'], $record['currency']);
            $commission = $amount * $this->getCommission($country);

            $report->add($commission);
        }

        return $report;
    }



    private function getCommission(string $country): float
    {
        return CountryData::isEu($country) ? self::COMMISSION_EU : self::COMMISSION_DEFAULT;
    }
}