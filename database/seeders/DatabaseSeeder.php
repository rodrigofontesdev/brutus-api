<?php

namespace Database\Seeders;

use App\Models\MagicLink;
use App\Models\Subscriber;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        Subscriber::factory()
            ->count(5)
            ->has(MagicLink::factory()->sequence(
                ['used_at' => null],
                ['used_at' => now()->toDateTimeString()]
            ))
            ->withIncompleteProfile()
            ->create();

        Subscriber::factory()
            ->count(5)
            ->has(MagicLink::factory()->used())
            ->has(MagicLink::factory()->used()->expired())
            ->create();
    }
}
