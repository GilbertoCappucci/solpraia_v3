<?php

namespace Database\Seeders;

use App\Models\Departament;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DepartamentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Departament::factory()->createMany([
            ['user_id' => 2, 'name' => 'Administration', 'active' => true],
            ['user_id' => 2, 'name' => 'Expedition', 'active' => true],
            ['user_id' => 2, 'name' => 'Bar', 'active' => true],
            ['user_id' => 2, 'name' => 'Kitchen', 'active' => true],
            ['user_id' => 2, 'name' => 'Finance', 'active' => true],
            ['user_id' => 2, 'name' => 'Service', 'active' => true],
        ]); 
    }
}
