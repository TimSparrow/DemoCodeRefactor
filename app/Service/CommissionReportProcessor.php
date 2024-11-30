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
        $lineNumber = 0;

        foreach ($this->reader->eachLine() as $line) {
            try {
                $record = json_decode($line, true);
                $this->validateRecord($record, ++$lineNumber);
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

    private function validateRecord(?array $record, int $line): void
    {
        if (null === $record) {
            throw new \UnexpectedValueException("Line '$line' is not a valid JSON record");
        }

        foreach(['bin', 'amount', 'currency'] as $key) {
            if (!array_key_exists($key, $record)) {
                throw new \UnexpectedValueException("Line '$line' is not a valid transaction, missing $key");
            }
        }
    }
}