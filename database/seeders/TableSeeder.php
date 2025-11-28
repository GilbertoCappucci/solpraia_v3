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
                'name' => 'GS-1',
            ],
            [
                'user_id' => 2,
                'number' => 2,
                'active' => true,
                'name' => 'GS-2',
            ],
            [
                'user_id' => 2,
                'number' => 3,
                'active' => true,
                'name' => 'Jangada-260',
            ],
            [
                'user_id' => 2,
                'number' => 4,
                'active' => true,
                'name' => 'GS-4',   
            ],
            [
                'user_id' => 2,
                'number' => 5,
                'active' => true,
                'name' => 'GS-5',
            ],
        ]);

        /*
        $users = User::where(['role' => RoleEnum::ADMIN->value, 'active' => true])->get();

        foreach ($users as $user) {
            // Create between 5 to 15 beach umbrellas for each user
            $tablesCount = Table::where('user_id', $user->id)->count();
            $numUmbrellas = 100 + $tablesCount;
            for ($i = $tablesCount; $i < $numUmbrellas; $i++) {
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
