<?php

namespace Test\Models;

use App\Models\Report;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use Random\Randomizer;

#[CoversClass(\App\Models\Report::class)]
class ReportTest extends MockeryTestCase {

    private Randomizer $randomizer;

    public function setUp(): void
    {
        parent::setUp();
        $this->randomizer = new Randomizer;
    }


    /**
     * A new report should be created as empty
     */
    public function testShouldCreateReport(): void {
        $report = new Report();
        $this->assertInstanceOf(Report::class, $report);
        $this->assertEmpty($report->getTransactions());
    }

    public function testShouldAddTransaction(): void {
        $transaction = $this->randomizer->getFloat(0.0, 3.0);

        $report = new Report();
        $report->add($transaction);

        $this->assertCount(1, $report->getTransactions());
        $this->assertContains($transaction, $report->getTransactions());
    }

    public function testShouldAddTransactionToNonEmptyReport(): void {
        $report = new Report();
        $transaction1 = $this->randomizer->getFloat(0.0, 3.0);

        $report->add($transaction1);
        $this->assertCount(1, $report->getTransactions());
        $this->assertContains($transaction1, $report->getTransactions());

        // check that adding a transaction does not delete existing ones
        $transaction2 = $this->randomizer->getFloat(0.0, 3.0);
        $report->add($transaction2);
        $this->assertCount(2, $report->getTransactions());
        $this->assertContains($transaction2, $report->getTransactions());
        $this->assertContains($transaction1, $report->getTransactions());
    }
}