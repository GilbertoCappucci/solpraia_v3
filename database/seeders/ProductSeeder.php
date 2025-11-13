<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        for ($i = 0; $i < 200; $i++) {
            $category = Category::whereNotNull('category_id')->inRandomOrder()->first();
            $user_id = $category->user_id;
            $category_id = $category->id;

            Product::factory()->create([
                'user_id' => $user_id,
                'category_id' => $category_id,
            ]);
        } 
    }
}
