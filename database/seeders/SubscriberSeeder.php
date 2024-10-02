<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class SubscriberSeeder extends Seeder
{
    /**
     * @return void
     */
    public function run(): void
    {
        DB::table('users')->insert([
            'id' => Str::uuid()->toString(),
            'role' => 'subscriber',
            'email' => 'doe@example.com',
            'full_name' => 'John Doe',
            'cnpj' => '45536395000180',
            'mobile_phone' => '11988889000',
            'created_at' => now(),
            'updated_at' => now()
        ]);
    }
}
