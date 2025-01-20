<?php
namespace App\Services;

class FinanceService
{
    public function calculateTotalDonations(array $donations): float
    {
        return array_sum(array_column($donations, 'amount'));
    }

    public function calculateMonthlyAverage(array $transactions): float
    {
        if (empty($transactions)) {
            return 0;
        }

        $total = array_sum(array_column($transactions, 'amount'));
        return $total / count($transactions);
    }

    public function calculateGrowthRate(float $previousMonth, float $currentMonth): float
    {
        if ($previousMonth == 0) {
            return 0;
        }

        return (($currentMonth - $previousMonth) / $previousMonth) * 100;
    }

    public function formatCurrency(float $amount): string
    {
        return number_format($amount, 2, ',', ' ') . ' €';
    }

    public function calculateYearlyProjection(float $currentMonth, float $growthRate): float
    {
        $monthlyGrowth = 1 + ($growthRate / 100);
        $yearlyTotal = 0;

        for ($i = 0; $i < 12; $i++) {
            $yearlyTotal += $currentMonth * pow($monthlyGrowth, $i);
        }

        return $yearlyTotal;
    }
}
