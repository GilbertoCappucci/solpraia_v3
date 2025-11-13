<?php

namespace Database\Seeders;

use App\Enums\RoleEnum;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CheckSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        foreach(User::where('role', RoleEnum::ADMIN->value)->get() as $user) {
            foreach ($user->tables as $table) {
                // Create between 1 to 5 checks for each table
                $numChecks = rand(1, 5);
                for ($i = 0; $i < $numChecks; $i++) {
                    \App\Models\Check::factory()->create([
                        'table_id' => $table->id,
                    ]);
                }
            }
        }
    }
}
