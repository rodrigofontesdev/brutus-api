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
                ['used_at' => now()],
                ['used_at' => null]
            ))
            ->withIncompleteProfile()
            ->create();

        Subscriber::factory()
            ->count(5)
            ->has(MagicLink::factory())
            ->create();
    }
}
