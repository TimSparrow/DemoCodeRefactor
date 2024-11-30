<?php


namespace App\Service;
use App\Exceptions\InvalidBinException;
use App\Models\Report;

class CommissionReportProcessor
{
    public const string BASE_CURRENCY = "EUR";


    public function __construct(
        private readonly BinValidationService  $binValidator,
        private readonly ExchangeRateInterface $exchangeRates,
        private readonly CommissionInterface $commissionService,
        private readonly ReaderInterface       $reader
    )
    {
    }


    public function createReport(): Report
    {
        $report = new Report();

        foreach ($this->reader->eachLine() as $line) {
            try {
                $record = json_decode($line, true);
                $country = $this->binValidator->getCountryByBinNumber($record['bin']);
                $amount = $this->exchangeRates->getAmountConverted((float)$record['amount'], $record['currency']);
                $commission = $this->commissionService->getTransactionCommission($amount, $country);

                $report->add($commission);
            } catch (InvalidBinException $exception) { // ignore records with invalid BIN
                fputs(STDERR, $exception->getMessage(). " processing BIN ". $record['bin'] . "\n");
                continue;
            }
        }

        return $report;
    }
}