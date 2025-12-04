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

        //category_id:8 - Coca-Cola
        Product::factory()->create([
            'user_id' => 2,
            'category_id' => 8,
            'name' => 'Coca-Cola',
            'description' => 'Refrigerante sabor cola',
            'price' => 5.00,
            'favorite' => false,
            'active' => true,
        ]);

        //category_id:6 - Suco de laranja
        Product::factory()->create([
            'user_id' => 2,
            'category_id' => 6,
            'name' => 'Suco de laranja',
            'description' => 'Suco natural de laranja',
            'price' => 7.00,
            'favorite' => false,
            'active' => true,
        ]);

        //category_id:7 - Agua mineral
        Product::factory()->create([
            'user_id' => 2,
            'category_id' => 7,
            'name' => 'Agua mineral sem gas',
            'description' => 'Agua mineral sem gas',
            'price' => 3.00,
            'favorite' => true,
            'active' => true,
        ]);

        //category_id:7 - Agua com gas
        Product::factory()->create([
            'user_id' => 2,
            'category_id' => 7,
            'name' => 'Agua mineral com gas',
            'description' => 'Agua mineral com gas',
            'price' => 3.50,
            'favorite' => false,
            'active' => true,
        ]);

        //category_id:3 - Batata frita tradicional
        Product::factory()->create([
            'user_id' => 2,
            'category_id' => 3,
            'name' => 'Batata frita tradicional',
            'description' => 'Batata frita tradicional crocante',
            'price' => 15.00,
            'favorite' => true,
            'active' => true,
        ]);
        
        //category_id:3 - Batata frita cheddar e bacon
        Product::factory()->create([
            'user_id' => 2,
            'category_id' => 3,
            'name' => 'Batata frita cheddar e bacon',
            'description' => 'Batata frita com cheddar e bacon crocante',
            'price' => 20.00,
            'favorite' => false,
            'active' => true,
        ]);


        /*
        for ($i = 0; $i < 10; $i++) {
            $category = Category::whereNotNull('category_id')->inRandomOrder()->first();
            $user_id = $category->user_id;
            $category_id = $category->id;

            Product::factory()->create([
                'user_id' => $user_id,
                'category_id' => $category_id,
            ]);
        }
        */ 
    }
}
