<?php

namespace Database\Seeders;

use App\Enums\RoleEnum;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {

        User::factory()->create([
            'id' => 1,
            'name' => 'User root',
            'email' => 'root@gmail.com',
            'remember_token' => null,
            'two_factor_secret' => null,
            'two_factor_recovery_codes' => null,
            'two_factor_confirmed_at' => null,
            'role' => RoleEnum::ROOT->value,
            'active' => true,
        ]);

        User::factory()->create([
            'id' => 2,
            'name' => 'User admin',
            'email' => 'admin@gmail.com',
            'remember_token' => null,
            'two_factor_secret' => null,
            'two_factor_recovery_codes' => null,
            'two_factor_confirmed_at' => null,
            'role' => RoleEnum::ADMIN->value,
            'active' => true,
        ]);

        /*
        for ($i = 0; $i < 2; $i++){
            User::factory()->create();
        }
        */
    }
}
