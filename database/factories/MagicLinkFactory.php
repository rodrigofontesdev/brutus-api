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
            'used_at' => null,
            'expires_at' => now()->addMinutes(5)->toDateTimeString(),
        ];
    }

    public function used(): static
    {
        return $this->state(fn (array $attributes) => [
            'used_at' => now()->toDateTimeString(),
        ]);
    }

    public function expired(): static
    {
        return $this->state(fn (array $attributes) => [
            'expires_at' => now()->subDay()->toDateTimeString(),
        ]);
    }
}
