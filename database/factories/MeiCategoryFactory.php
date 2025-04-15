<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\MeiCategory>
 */
class MeiCategoryFactory extends Factory
{
    public function definition(): array
    {
        return [
            'type' => fake()->randomElement(['MEI-GERAL', 'MEI-TAC']),
            'creation_date' => fake()->dateTimeBetween(
                    startDate: Carbon::createFromDate(2023, 1, 25)->toDateString()
                )
                ->format('Y-m-d'),
            'table_a_excluded_after_032022' => false
        ];
    }

    public function tac(): static
    {
        return $this->state(fn () => [
            'type' => 'MEI-TAC',
            'creation_date' => Carbon::createFromDate(2023, 1, 10)->format('Y-m-d'),
            'table_a_excluded_after_032022' => false
        ]);
    }

    public function geral(): static
    {
        return $this->state(fn () => [
            'type' => 'MEI-GERAL',
            'creation_date' => Carbon::createFromDate(2024, 1, 15)->format('Y-m-d'),
            'table_a_excluded_after_032022' => false
        ]);
    }

    public function excludedTableA(): static
    {
        return $this->state(fn () => [
            'type' => 'MEI-TAC',
            'creation_date' => Carbon::createFromDate(2022, 2, 13)->format('Y-m-d'),
            'table_a_excluded_after_032022' => true
        ]);
    }
}
