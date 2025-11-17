<?php

namespace Database\Seeders;

use App\Models\Employee;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class EmployeeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {

        Employee::factory()->create([
            'id' => 1,
            'user_id' => 2,
            'name' => 'João Silva',
            'active' => true,
        ]);

        Employee::factory()->create([
            'id' => 2,
            'user_id' => 2,
            'name' => 'Maria Oliveira',
            'active' => true,
        ]);

        Employee::factory()->create([
            'id' => 3,
            'user_id' => 2,
            'name' => 'Carlos Souza',
            'active' => true,
        ]);

        /*
        for ($i = 0; $i < 50; $i++) {
            Employee::factory()->create();
        }
        */
    }
}
