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
                    startDate: Carbon::today()->subYears(2)->startOfYear()->toDateString(),
                    endDate: '-1 month'
                )
                ->format('Y-m-d'),
        ];
    }

    public function withoutTradeInvoice(): static
    {
        return $this->state(fn () => [
            'trade_with_invoice' => 0,
            'trade_without_invoice' => 0,
        ]);
    }

    public function withoutIndustryInvoice(): static
    {
        return $this->state(fn () => [
            'industry_with_invoice' => 0,
            'industry_without_invoice' => 0,
        ]);
    }

    public function withoutServicesInvoice(): static
    {
        return $this->state(fn () => [
            'services_with_invoice' => 0,
            'services_without_invoice' => 0,
        ]);
    }

    public function onlyTradeInvoice(): static
    {
        return $this->state(fn () => [
            'industry_with_invoice' => 0,
            'industry_without_invoice' => 0,
            'services_with_invoice' => 0,
            'services_without_invoice' => 0,
        ]);
    }

    public function onlyIndustryInvoice(): static
    {
        return $this->state(fn () => [
            'trade_with_invoice' => 0,
            'trade_without_invoice' => 0,
            'services_with_invoice' => 0,
            'services_without_invoice' => 0,
        ]);
    }

    public function onlyServicesInvoice(): static
    {
        return $this->state(fn () => [
            'trade_with_invoice' => 0,
            'trade_without_invoice' => 0,
            'industry_with_invoice' => 0,
            'industry_without_invoice' => 0,
        ]);
    }
}
