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
        //id:1 - user_id:2 - Main Menu
        Menu::factory()->create([
            'user_id' => 2,
            'name' => 'Main Menu',
            'active' => true,
        ]);

        //id:2 - user_id:2 - Secondary Menu
        Menu::factory()->create([
            'user_id' => 2,
            'name' => 'Secondary Menu',
            'active' => false,
        ]);

        //id:3 - user_id:5 - Main Menu
        Menu::factory()->create([
            'user_id' => 5,
            'name' => 'Main Menu',
            'active' => true,
        ]);

        /*
        for ($i = 0; $i < 15; $i++) {
            Menu::factory()->create();
        }
        */
    }
}
