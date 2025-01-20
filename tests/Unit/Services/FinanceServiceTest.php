<?php
namespace Tests\Unit\Services;

use App\Services\FinanceService;
use PHPUnit\Framework\TestCase;

class FinanceServiceTest extends TestCase
{
    private $financeService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->financeService = new FinanceService();
    }

    public function testCalculateTotalDonations()
    {
        $donations = [
            ['amount' => 100.50],
            ['amount' => 200.75],
            ['amount' => 50.25]
        ];

        $total = $this->financeService->calculateTotalDonations($donations);
        $this->assertEquals(351.50, $total);
    }

    public function testCalculateMonthlyAverage()
    {
        $transactions = [
            ['amount' => 1000, 'date' => '2025-01-01'],
            ['amount' => 2000, 'date' => '2025-01-15'],
            ['amount' => 1500, 'date' => '2025-01-30']
        ];

        $average = $this->financeService->calculateMonthlyAverage($transactions);
        $this->assertEquals(1500, $average);
    }

    public function testCalculateGrowthRate()
    {
        $previousMonth = 1000;
        $currentMonth = 1200;

        $growthRate = $this->financeService->calculateGrowthRate($previousMonth, $currentMonth);
        $this->assertEquals(20, $growthRate);
    }

    public function testFormatCurrency()
    {
        $amount = 1234.56;
        $formatted = $this->financeService->formatCurrency($amount);
        $this->assertEquals('1 234,56 €', $formatted);
    }

    public function testCalculateYearlyProjection()
    {
        $currentMonth = 1000;
        $growthRate = 5;

        $projection = $this->financeService->calculateYearlyProjection($currentMonth, $growthRate);
        $this->assertEquals(12600, $projection);
    }
}
