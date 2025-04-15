<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class UserFactory extends Factory
{
    public function definition(): array
    {
        return [
            'role' => 'subscriber',
            'email' => fake()->unique()->safeEmail(),
            'full_name' => fake()->name(),
            'cnpj' => fake()->numerify('##############'),
            'mobile_phone' => fake()->numerify('###########'),
            'city' => fake()->city(),
            'state' => fake()->randomElement(['SP', 'RJ', 'MG']),
            'secret_word' => fake()->word(),
        ];
    }

    public function withIncompleteProfile(): static
    {
        return $this->state(fn () => [
            'city' => null,
            'state' => null,
            'secret_word' => null,
        ]);
    }
}
