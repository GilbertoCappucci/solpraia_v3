<?php

namespace Database\Seeders;

use App\Models\Menu;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class MenuSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Menu::factory()->create([
            'user_id' => 2,
            'name' => 'Main Menu',
            'active' => true,
        ]);

        for ($i = 0; $i < 15; $i++) {
            Menu::factory()->create();
        }
    }
}
