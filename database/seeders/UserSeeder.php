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
        // Create admin
        User::factory()->create([
            'id' => 1,
            'user_id' => 1,
            'name' => 'User admin',
            'email' => 'sol@gmail.com',
            'password' => '45rtfgvb',
            'email_verified_at' => now(),
            'remember_token' => null,
            'two_factor_secret' => null,
            'two_factor_recovery_codes' => null,
            'two_factor_confirmed_at' => null,
            'role' => RoleEnum::ADMIN->value,
            'active' => true,
        ]);

        //User 1 normal
        User::factory()->create([
            'id' => 2,
            'user_id' => 1,
            'name' => 'User normal',
            'email' => 'sol1@gmail.com',
            'password' => '45rtfgvb',
            'email_verified_at' => now(),
            'password' => '45rtfgvb',
            'remember_token' => null,
            'two_factor_secret' => null,
            'two_factor_recovery_codes' => null,
            'two_factor_confirmed_at' => null,
            'role' => RoleEnum::DEVICE->value,
            'active' => true,
        ]);

        //Create user 2 normal
        User::factory()->create([
            'id' => 3,
            'user_id' => 1,
            'name' => 'User normal 2',
            'email' => 'sol2@gmail.com',
            'password' => '45rtfgvb',
            'email_verified_at' => now(),
            'remember_token' => null,
            'two_factor_secret' => null,
            'two_factor_recovery_codes' => null,
            'two_factor_confirmed_at' => null,
            'role' => RoleEnum::DEVICE->value,
            'active' => true,
        ]);

        // Create admin 2
        User::factory()->create([
            'id' => 4,
            'user_id' => 4,
            'name' => 'User admin 2',
            'email' => 'admin2@gmail.com',
            'password' => '45rtfgvb',
            'email_verified_at' => now(),
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
