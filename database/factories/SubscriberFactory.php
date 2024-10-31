<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Model>
 */
class SubscriberFactory extends Factory
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
            'mei' => fake()->randomElement(['MEI-GERAL', 'MEI-TAC']),
            'secret_word' => fake()->word(),
            'email_verified_at' => now()->toDateTimeString(),
        ];
    }

    public function unverified(): static
    {
        return $this->state(fn(array $attributes) => [
            'email_verified_at' => null,
        ]);
    }

    public function withIncompleteProfile(): static
    {
        return $this->state(fn(array $attributes) => [
            'city' => null,
            'state' => null,
            'mei' => null,
            'secret_word' => null,
        ]);
    }
}
