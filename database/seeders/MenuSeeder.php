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
        //id:1 - admin_id:1 - Main Menu
        Menu::factory()->create([
            'admin_id' => 1,
            'menu_id' => null,
            'name' => 'Principal',
            'active' => true,
        ]);

        //id:2 - admin_id:1 - Secondary Menu
        Menu::factory()->create([
            'admin_id' => 1,
            'menu_id' => 1,
            'name' => 'Alta temporada',
            'active' => true,
        ]);

        //id:3 - admin_id:1 - Secondary Menu
        Menu::factory()->create([
            'admin_id' => 1,
            'menu_id' => 1,
            'name' => 'Baixa temporada',
            'active' => true,
        ]);

        /*
        for ($i = 0; $i < 15; $i++) {
            Menu::factory()->create();
        }
        */
    }
}
