<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\MagicLink>
 */
class MagicLinkFactory extends Factory
{
    public function definition(): array
    {
        return [
            'token' => fake()->uuid(),
            'used_at' => now(),
            'expires_at' => now()->addMinutes(5)
        ];
    }

    public function unused(): static
    {
        return $this->state(fn(array $attributes) => [
            'used_at' => null,
        ]);
    }

    public function expired(): static
    {
        return $this->state(fn(array $attributes) => [
            'expires_at' => now()->subDay()
        ]);
    }
}
