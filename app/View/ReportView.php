<?php

namespace App\View;

use App\Models\Report;

class ReportView
{
    public function __construct(private readonly Report  $report) {}

    public function show(): void
    {
        foreach ($this->report->getTransactions() as $report) {
            echo $report . PHP_EOL;
        }
    }

}