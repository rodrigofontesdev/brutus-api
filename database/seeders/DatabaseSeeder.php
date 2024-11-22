<?php

namespace Database\Seeders;

use App\Models\MagicLink;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        User::factory()
            ->count(5)
            ->has(
                MagicLink::factory()->sequence(
                    ['used_at' => null],
                    ['used_at' => now()->toDateTimeString()]
                )
            )
            ->withIncompleteProfile()
            ->create();

        User::factory()
            ->count(5)
            ->has(MagicLink::factory()->used())
            ->has(MagicLink::factory()->used()->expired())
            ->create();
    }
}
