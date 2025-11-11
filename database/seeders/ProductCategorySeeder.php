<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\ProductCategory;

class ProductCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {

        ProductCategory::factory()->create(
            [
                'user_id' => 2,
                'parent_id' => null,
                'name' => 'Porções',
                'description' => 'Porções de alimentos e bebidas.'
            ],
        );

        ProductCategory::factory()->create(
            [
                'user_id' => 2,
                'parent_id' => null,
                'name' => 'Bebidas',
                'description' => 'Bebidas alcoólicas e não alcoólicas.'
            ],
        );

        ProductCategory::factory()->create(
            [
                'user_id' => 2,
                'parent_id' => null,
                'name' => 'Sobremesas',
                'description' => 'Doces e sobremesas variadas.'
            ],
        );

        for ($i = 0; $i < 20; $i++) {
            ProductCategory::factory()->create();
        }
    }
}
