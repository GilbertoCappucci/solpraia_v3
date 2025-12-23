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
            'remember_token' => null,
            'two_factor_secret' => null,
            'two_factor_recovery_codes' => null,
            'two_factor_confirmed_at' => null,
            'role' => RoleEnum::ADMIN->value,
            'active' => true,
        ]);

        //Create device 001
        User::factory()->create([
            'id' => 2,
            'user_id' => 1,
            'name' => 'Device 001',
            'email' => 'sol1@gmail.com',
            'email_verified_at' => now(),
            'password' => '45rtfgvb',
            'remember_token' => null,
            'two_factor_secret' => null,
            'two_factor_recovery_codes' => null,
            'two_factor_confirmed_at' => null,
            'role' => RoleEnum::DEVICE->value,
            'active' => true,
        ]);

        //Create device 002
        User::factory()->create([
            'id' => 3,
            'user_id' => 1,
            'name' => 'Device 002',
            'email' => 'sol2@gmail.com',
            'email_verified_at' => now(),
            'password' => '45rtfgvb',
            'remember_token' => null,
            'two_factor_secret' => null,
            'two_factor_recovery_codes' => null,
            'two_factor_confirmed_at' => null,
            'role' => RoleEnum::DEVICE->value,
            'active' => true,
        ]);

        // Create device 003
        User::factory()->create([
            'id' => 4,
            'user_id' => 1,
            'name' => 'Device 003',
            'email' => 'sol3@gmail.com',
            'email_verified_at' => now(),
            'password' => '45rtfgvb',
            'remember_token' => null,
            'two_factor_secret' => null,
            'two_factor_recovery_codes' => null,
            'two_factor_confirmed_at' => null,
            'role' => RoleEnum::DEVICE->value,
            'active' => true,
        ]);
        
        /*
        for ($i = 0; $i < 2; $i++){
            User::factory()->create();
        }
        */
    }
}
