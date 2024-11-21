<?php

namespace Database\Factories;

use App\Helpers\Generator;
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
            'expires_at' => Generator::magicLinkExpireTime(),
        ];
    }

    public function used(): static
    {
        return $this->state(fn () => [
            'used_at' => now()->toDateTimeString(),
        ]);
    }

    public function expired(): static
    {
        return $this->state(fn () => [
            'expires_at' => now()->subDay()->toDateTimeString(),
        ]);
    }
}
