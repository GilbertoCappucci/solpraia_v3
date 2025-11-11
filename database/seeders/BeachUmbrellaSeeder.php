<?php

namespace Database\Seeders;

use App\Models\beachUmbrella;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class BeachUmbrellaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        for ($i = 0; $i < 100; $i++) {
            beachUmbrella::factory()->create();
        }
    }
}
