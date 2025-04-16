<?php

namespace App\Events;

use App\Models\MeiCategory;
use App\Models\Report;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Contracts\Events\ShouldDispatchAfterCommit;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AnnualRevenueChanged implements ShouldBroadcast, ShouldDispatchAfterCommit
{
    use Dispatchable;
    use InteractsWithSockets;
    use SerializesModels;

    /**
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('annual-revenue.'.Auth::id()),
        ];
    }

    public function broadcastAs(): string
    {
        return 'annual-revenue-changed';
    }

    /**
     * @return array<string, mixed>
     */
    public function broadcastWith(): array
    {
        $meiCategories = MeiCategory::where('user', Auth::id())->latest('creation_date')->get();

        if (0 === $meiCategories->count()) {
            return [];
        }

        $database = DB::connection()->getDriverName();
        $yearExpression = match ($database) {
            'sqlite' => "strftime('%Y', period)", // Pest uses SQLite as database
            default => 'EXTRACT(YEAR FROM period)',
        };
        $reportsByPeriod = Report::where('user', Auth::id())
            ->select(
                DB::raw("$yearExpression AS year"),
                DB::raw('SUM(
                    COALESCE("trade_with_invoice", 0) + COALESCE("trade_without_invoice", 0) +
                    COALESCE("industry_with_invoice", 0) + COALESCE("industry_without_invoice", 0) +
                    COALESCE("services_with_invoice", 0) + COALESCE("services_without_invoice", 0)
                ) AS "total"')
            )
            ->groupBy('year')
            ->orderBy('year', 'desc')
            ->get();

        $openingDate = Carbon::parse($meiCategories->last()->creation_date);
        $years = Carbon::create($openingDate->year, 1, 1)->yearsUntil(Carbon::create(null, 1, 1));
        $annualRevenues = [];

        foreach ($years as $date) {
            $firstDayOfYear = Carbon::create($date->year, 1, 1);
            $lastDayOfYear = Carbon::create($date->year, 12, 31);
            $categories = $meiCategories->filter(
                function (MeiCategory $category) use ($lastDayOfYear) {
                    return Carbon::parse($category->creation_date)->startOfDay()->lessThanOrEqualTo($lastDayOfYear);
                }
            );

            $latestCategory = $categories->first();
            $isCurrentYear2022 = 2022 === $date->year;
            $isCategoryNeverChanged = 1 === $categories->count();
            $latestCategoryCreationDate = Carbon::parse($latestCategory->creation_date)->startOfDay();
            $categoryLimit = MeiCategory::TAC === $latestCategory->type ? MeiCategory::TAC_LIMIT : MeiCategory::GERAL_LIMIT;
            $defaultStartDate = $latestCategoryCreationDate->isSameYear($firstDayOfYear) ? $latestCategoryCreationDate : $firstDayOfYear;
            $isMeiTacCreatedOrChangedUntilMarch2022 = MeiCategory::TAC === $latestCategory->type && $latestCategoryCreationDate->isBetween(Carbon::create(2022, 1, 1), Carbon::create(2022, 3, 31));

            if ($isCategoryNeverChanged) {
                $annualLimit = calculateAnnualRevenue($categoryLimit, $defaultStartDate, $lastDayOfYear);

                // Criou categoria em 2022 até 31 de março
                if ($isMeiTacCreatedOrChangedUntilMarch2022) {
                    $annualLimit = calculateAnnualRevenue($categoryLimit, $firstDayOfYear, $lastDayOfYear);
                }
            } else {
                $penultimateCategory = $categories->after($latestCategory);
                $penultimateCategoryCreationDate = Carbon::parse($penultimateCategory->creation_date)->startOfDay();
                $oldCategoryLimit = MeiCategory::TAC === $penultimateCategory->type ? MeiCategory::TAC_LIMIT : MeiCategory::GERAL_LIMIT;
                $isCategoryChangedInSameYear = $latestCategoryCreationDate->isSameYear($penultimateCategoryCreationDate);
                $isTransitionYear = $latestCategoryCreationDate->isSameYear($firstDayOfYear);

                // Alterou categoria em 2022 até 31 de março
                $annualLimit = calculateAnnualRevenue($categoryLimit, $firstDayOfYear, $lastDayOfYear);

                // Alterou categoria em 2022 ou alterou e excluiu a Tabela A, depois de 31 de março
                if ($isCurrentYear2022 && $latestCategory->table_a_excluded_after_032022) {
                    $annualLimit = calculateAnnualRevenue($oldCategoryLimit, $firstDayOfYear, $lastDayOfYear);
                }

                // Alterou categoria no mesmo ano
                if ($isCategoryChangedInSameYear && $isTransitionYear) {
                    $annualLimit = calculateAnnualRevenue($oldCategoryLimit, $penultimateCategoryCreationDate, $lastDayOfYear);
                }

                // Alterou categoria em anos diferentes
                if (!$isCurrentYear2022 && !$isCategoryChangedInSameYear && $isTransitionYear) {
                    $annualLimitAsOldCategory = calculateAnnualRevenue($oldCategoryLimit, $firstDayOfYear, Carbon::parse($latestCategory->creation_date)->subMonth());
                    $annualLimitAsNewCategory = calculateAnnualRevenue($categoryLimit, $latestCategoryCreationDate, $lastDayOfYear);
                    $annualLimit = $annualLimitAsOldCategory + $annualLimitAsNewCategory;
                }
            }

            if ($reportsByPeriod->contains('year', $date->year)) {
                $period = $reportsByPeriod->firstWhere('year', $date->year);
                $isLimitExceeded = $period->total > $annualLimit;
                $status = match (true) {
                    $period->total > $annualLimit * 1.2 => 'beyond',
                    $isLimitExceeded => 'above',
                    default => 'below',
                };

                array_unshift($annualRevenues, [
                    'year' => $date->year,
                    'total' => $period->total,
                    'limit' => $annualLimit,
                    'limit_exceeded' => $isLimitExceeded,
                    'status' => $status,
                ]);
            } else {
                array_unshift($annualRevenues, [
                    'year' => $date->year,
                    'total' => 0,
                    'limit' => $annualLimit,
                    'limit_exceeded' => false,
                    'status' => 'below',
                ]);
            }
        }

        return $annualRevenues;
    }
}
