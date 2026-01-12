<?php

namespace Database\Seeders;

use App\Enums\RoleEnum;
use App\Enums\TableStatusEnum;
use App\Models\Check;
use App\Models\Tab;
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


        Table::factory()->create(
            [
                'admin_id' => 1,
                'number' => 1,
                'name' => 'Venda Direta',
            ]
        );

        Check::factory()->create(
            [
                'admin_id' => 1,
                'table_id' => 1,
            ]
        );

        Table::factory()->create(   
            [
                'admin_id' => 1,
                'number' => 2,
            ]
        );

        Check::factory()->create(
            [
                'admin_id' => 1,
                'table_id' => 2,
            ]
        );

        Table::factory()->create(   
            [
                'admin_id' => 1,
                'number' => 3,
            ]
        );

        Check::factory()->create(
            [
                'admin_id' => 1,
                'table_id' => 3,
            ]
        );

        Table::factory()->create(   
            [
                'admin_id' => 1,
                'number' => 4,
            ]
        );
        

        Check::factory()->create(
            [
                'admin_id' => 1,
                'table_id' => 4,
            ]
        );
    }
}
