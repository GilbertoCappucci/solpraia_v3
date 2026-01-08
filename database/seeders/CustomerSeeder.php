<?php

namespace Database\Seeders;

use App\Models\Customer;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Pest\ArchPresets\Custom;

class CustomerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Customer::factory()->create([
            'name' => 'John Doe',
            'phone' => '123-456-7890',
            'email' => 'john.doe@example.com',
            'is_active' => true,
            'notes' => 'Important customer',
        ]);
    }
}
