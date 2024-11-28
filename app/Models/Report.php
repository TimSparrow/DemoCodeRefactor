<?php

namespace App\Models;

class Report
{
    private array $transactions = [];

    public function __construct(){}

    public function add(float $transaction): void
    {
        $this->transactions[] = $transaction;
    }

    public function getTransactions(): array
    {
        return $this->transactions;
    }
}