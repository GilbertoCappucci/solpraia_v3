<?php

namespace Database\Seeders;

use App\Enums\RoleEnum;
use App\Models\Table;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class TableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {


        Table::factory()->createMany([
            [
                'user_id' => 2,
                'number' => 1,
                'active' => true,
            ],
            [
                'user_id' => 2,
                'number' => 2,
                'active' => true,
            ],
            [
                'user_id' => 2,
                'number' => 3,
                'active' => true,
            ],
            [
                'user_id' => 2,
                'number' => 4,
                'active' => true,
            ],
            [
                'user_id' => 2,
                'number' => 5,
                'active' => true,
            ],
        ]);

        /*
        $users = User::where(['role' => RoleEnum::ADMIN->value, 'active' => true])->get();

        foreach ($users as $user) {
            // Create between 5 to 15 beach umbrellas for each user
            $numUmbrellas = rand(2, 5);
            for ($i = 0; $i < $numUmbrellas; $i++) {
                Table::factory()->create([
                    'user_id' => $user->id,
                    'number' => $i + 1,
                    'active' => true,
                ]);
            }
        }
        */
    }
}
