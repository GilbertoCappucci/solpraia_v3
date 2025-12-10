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

        //id:1 - category_id:8 - Coca-Cola
        Product::factory()->create([
            'category_id' => 8,
            'name' => 'Coca-Cola',
            'description' => 'Refrigerante sabor cola',
            'price' => 5.00,
            'favorite' => false,
            'active' => true,
        ]);

        //id:2 - category_id:6 - Suco de laranja
        Product::factory()->create([
            'category_id' => 6,
            'name' => 'Suco de laranja',
            'description' => 'Suco natural de laranja',
            'price' => 7.00,
            'favorite' => false,
            'active' => true,
        ]);

        //id:3 - category_id:7 - Agua mineral
        Product::factory()->create([
            'category_id' => 7,
            'name' => 'Agua mineral sem gas',
            'description' => 'Agua mineral sem gas',
            'price' => 3.00,
            'favorite' => true,
            'active' => true,
        ]);

        //id:4 - category_id:7 - Agua com gas
        Product::factory()->create([
            'category_id' => 7,
            'name' => 'Agua mineral com gas',
            'description' => 'Agua mineral com gas',
            'price' => 3.50,
            'favorite' => false,
            'active' => true,
        ]);

        //id:5 - category_id:3 - Batata frita tradicional
        Product::factory()->create([
            'category_id' => 3,
            'name' => 'Batata frita tradicional',
            'description' => 'Batata frita tradicional crocante',
            'price' => 15.00,
            'favorite' => true,
            'active' => true,
        ]);
        
        //id:6 - category_id:3 - Batata frita cheddar e bacon
        Product::factory()->create([
            'category_id' => 3,
            'name' => 'Batata frita cheddar e bacon',
            'description' => 'Batata frita com cheddar e bacon crocante',
            'price' => 20.00,
            'favorite' => false,
            'active' => true,
        ]);

        //id:7 - category_id:11 - Fritas
        Product::factory()->create([
            'category_id' => 11,
            'name' => 'Fritas',
            'description' => 'Fritas crocantes',
            'price' => 6.00,
            'favorite' => true,
            'active' => true,
        ]);

        //id:8 - category_id:12 - Bebida não alcoólica
        Product::factory()->create([
            'category_id' => 12,
            'name' => 'Coca-Cola',
            'description' => 'Refrigerante sabor cola',
            'price' => 5.00,
            'favorite' => true,
            'active' => true,
        ]);
        
    }
}
