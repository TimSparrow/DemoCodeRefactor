#!/bin/php
<?php

include_once __DIR__ . '/bootstrap/bootstrap.php';

use App\Service\BinListNetValidator;
use App\Service\CommissionCalculator;
use App\Service\ExchangeRateFetcher;
use App\Service\FileReaderIterator;
use App\View\ReportView;
use App\Service\CommissionReportProcessor;
use GuzzleHttp\Client;

$source = $argv[1];
$apiKey = getenv('EXCHANGE_RATES_API_KEY');

// The following should normally be initialized by autowiring
$client = new Client();
$binValidator = new BinListNetValidator($client);
$exchange = new ExchangeRateFetcher($client, CommissionReportProcessor::BASE_CURRENCY, $apiKey);
$commissionCalculator = new CommissionCalculator();
$reader = new FileReaderIterator($source);

try {
    $reporter = new CommissionReportProcessor( $binValidator, $exchange, $commissionCalculator, $reader);
    $report = $reporter->createReport();
    $view = new ReportView($report);
    $view->show();
} catch (\Throwable $e) {
    fputs (STDERR, get_class($e) . ':' . $e->getMessage(). "\n");
    exit($e->getCode() ?: 255); // something went wrong
}