<?php

namespace Database\Seeders;

use App\Enums\CheckStatusEnum;
use App\Enums\RoleEnum;
use App\Models\Check;
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

        /*
        foreach(User::where('role', RoleEnum::ADMIN->value)->get() as $user) {

            $tables = $user->tables()->where('active', true)->get();
        
            foreach ($tables as $table) {
            
                Check::factory()->create([
                    'table_id' => $table->id,
                    'total' => 0,
                    'status' => CheckStatusEnum::OPEN->value,
                    'opened_at' => now(),
                    'closed_at' => null,
                ]);
            }
        }
        */
    }
}
