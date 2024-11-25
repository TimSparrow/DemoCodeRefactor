#!/bin/php
<?php

include_once __DIR__ . '/bootstrap/bootstrap.php';

use View\ReportView;
use Service\CsvReporter

$source = $argv[1];

$reporter = new CsvReporter($source);
$report = $reporter->createReport();
$view = new ReportView($report);
$view->show();
