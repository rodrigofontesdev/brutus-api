<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Report>
 */
class ReportFactory extends Factory
{
    private const MIN_AMOUNT_IN_CENTS = 100;
    private const MAX_AMOUNT_IN_CENTS = 675000;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'trade_with_invoice' => fake()->numberBetween(
                self::MIN_AMOUNT_IN_CENTS,
                self::MAX_AMOUNT_IN_CENTS
            ),
            'trade_without_invoice' => fake()->numberBetween(
                self::MIN_AMOUNT_IN_CENTS,
                self::MAX_AMOUNT_IN_CENTS
            ),
            'industry_with_invoice' => fake()->numberBetween(
                self::MIN_AMOUNT_IN_CENTS,
                self::MAX_AMOUNT_IN_CENTS
            ),
            'industry_without_invoice' => fake()->numberBetween(
                self::MIN_AMOUNT_IN_CENTS,
                self::MAX_AMOUNT_IN_CENTS
            ),
            'services_with_invoice' => fake()->numberBetween(
                self::MIN_AMOUNT_IN_CENTS,
                self::MAX_AMOUNT_IN_CENTS
            ),
            'services_without_invoice' => fake()->numberBetween(
                self::MIN_AMOUNT_IN_CENTS,
                self::MAX_AMOUNT_IN_CENTS
            ),
            'period' => fake()
                ->dateTimeBetween(
                    startDate: Carbon::today()->subYear()->setDay(1)->setMonth(1)->toDateString(),
                    endDate: '-1 month'
                )
                ->format('Y-m-d'),
        ];
    }

    public function withoutTradeInvoice(): static
    {
        return $this->state(fn () => [
            'trade_with_invoice' => null,
            'trade_without_invoice' => null,
        ]);
    }

    public function withoutIndustryInvoice(): static
    {
        return $this->state(fn () => [
            'industry_with_invoice' => null,
            'industry_without_invoice' => null,
        ]);
    }

    public function withoutServicesInvoice(): static
    {
        return $this->state(fn () => [
            'services_with_invoice' => null,
            'services_without_invoice' => null,
        ]);
    }
}
