<?php

namespace App\Models;

class Report
{
    private array $transactions = [];

    public function __construct(){}

    public function add(float $transaction){
        $this->transactions[] = $transaction;
    }

    public function getTransactions(){
        return $this->transactions;
    }
}