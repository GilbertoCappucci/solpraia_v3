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
            'pix_enabled' => false,
            'pix_key' => null,
            'pix_key_type' => null,
            'pix_name' => null,
            'pix_city' => null,
            'time_limit_pending' => 10,
            'time_limit_in_production' => 10,
            'time_limit_in_transit' => 10,
            'time_limit_closed' => 10,
            'time_limit_releasing' => 10,
            'polling_interval' => 5000,
        ]);
    }
}
