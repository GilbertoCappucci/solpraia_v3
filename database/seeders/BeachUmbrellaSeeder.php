<?php

namespace Database\Seeders;

use App\Enums\RoleEnum;
use App\Models\BeachUmbrella;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class BeachUmbrellaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = User::where(['role' => RoleEnum::ADMIN->value, 'active' => true])->get();

        foreach ($users as $user) {
            // Create between 5 to 15 beach umbrellas for each user
            $numUmbrellas = rand(5, 15);
            for ($i = 0; $i < $numUmbrellas; $i++) {
                BeachUmbrella::factory()->create([
                    'user_id' => $user->id,
                ]);
            }
        }
    }
}
