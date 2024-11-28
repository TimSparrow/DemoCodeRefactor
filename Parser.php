#!/bin/php
<?php

include_once __DIR__ . '/bootstrap/bootstrap.php';

use App\Service\BinListNetValidator;
use App\Service\ExchangeRateFetcher;
use App\View\ReportView;
use App\Service\CommissionCalculator;
use GuzzleHttp\Client;

$source = $argv[1];

// The following should normally be initialized by autowiring
$client = new Client();
$binValidator = new BinListNetValidator($client);
$exchange = new ExchangeRateFetcher($client, CommissionCalculator::BASE_CURRENCY, getenv('EXCHANGE_RATES_API_KEY'));
$reader = new \App\Service\FileReaderIterator($source);

try {
    $reporter = new CommissionCalculator( $binValidator, $exchange, $reader,);
    $report = $reporter->createReport();
    $view = new ReportView($report);
    $view->show();
} catch (Exception $e) {
    die ($e->getMessage());
}