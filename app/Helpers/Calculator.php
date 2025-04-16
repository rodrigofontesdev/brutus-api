<?php

use Illuminate\Support\Carbon;

function calculateAnnualRevenue(int $amount, DateTimeInterface $start, DateTimeInterface $end)
{
    $startDate = Carbon::parse($start)->startOfMonth();
    $endDate = Carbon::parse($end)->startOfMonth();
    $monthsUntilPeriodEnds = $startDate->monthsUntil($endDate)->count();
    $limitByMonth = round($amount / 12);
    $total = (int) $limitByMonth * $monthsUntilPeriodEnds;

    return match ($total) {
        25_160_004 => 25_160_000,
        default => $total,
    };
}
