<?php

namespace Database\Seeders;

use App\Models\GlobalSetting;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class GlobalSettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        GlobalSetting::factory()->create([
            'admin_id' => 1,
            'menu_id' => 1,
            'pix_enabled' => true,
            'pix_key' => 12686075848,
            'pix_key_type' => 'CPF',
            'pix_name' => 'Gilberto Cappucci Junior',
            'pix_city' => 'São Paulo',
            'time_limit_pending' => 10,
            'time_limit_in_production' => 10,
            'time_limit_in_transit' => 10,
            'time_limit_closed' => 10,
            'time_limit_releasing' => 10,
            'polling_interval' => 5000,
        ]);
    }
}
