#!/bin/php
<?php

include_once __DIR__ . '/bootstrap/bootstrap.php';

use App\Service\BinListNetValidator;
use App\Service\ExchangeRateFetcher;
use App\View\ReportView;
use App\Service\CsvReporter;
use GuzzleHttp\Client;

$source = $argv[1];

// The following should normally be initialized by autowiring
$client = new Client();
$binValidator = new BinListNetValidator($client);
$exchange = new ExchangeRateFetcher();
try {
    $reporter = new CsvReporter($source, $binValidator, $exchange);
    $report = $reporter->createReport();
    $view = new ReportView($report);
    $view->show();
} catch (Exception $e) {
    die ($e->getMessage());
}