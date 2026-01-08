<?php

namespace Database\Seeders;

use App\Enums\RoleEnum;
use App\Enums\TableStatusEnum;
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
                'admin_id' => 1,
                'number' => 1,
                'name' => 'Venda Direta',
                'status' => TableStatusEnum::FREE->value,
            ],
            [
                'admin_id' => 1,
                'number' => 2,
                'status' => TableStatusEnum::FREE->value,
            ],
            [
                'admin_id' => 1,
                'number' => 3,
                'status' => TableStatusEnum::FREE->value,
            ],
            [
                'admin_id' => 1,
                'number' => 4,
                'status' => TableStatusEnum::FREE->value,  
            ],
            [
                'admin_id' => 1,
                'number' => 5,
                'status' => TableStatusEnum::FREE->value,
            ],
        ]);

        /*
        $users = User::where(['role' => RoleEnum::ADMIN->value, 'active' => true])->get();

        foreach ($users as $user) {
            // Create between 5 to 15 beach umbrellas for each user
            $tablesCount = Table::where('admin_id', $user->id)->count();
            $numUmbrellas = 100 + $tablesCount;
            for ($i = $tablesCount; $i < $numUmbrellas; $i++) {
                Table::factory()->create([
                    'admin_id' => $user->id,
                    'number' => $i + 1,
                    'active' => true,
                ]);
            }
        }
        */
        
    }
}
