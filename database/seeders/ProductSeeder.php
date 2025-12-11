<?php

namespace Database\Seeders;

use App\Enums\DepartamentEnum;
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

        //id:1 - category_id:8 - Coca-Cola - Bar
        Product::factory()->create([
            'category_id' => 8,
            'production_local' => DepartamentEnum::BAR,
            'name' => 'Coca-Cola',
            'description' => 'Refrigerante sabor cola',
            'price' => 5.00,
            'favorite' => false,
            'active' => true,
        ]);

        //id:2 - category_id:6 - Suco de laranja - Bar
        Product::factory()->create([
            'category_id' => 6,
            'production_local' => DepartamentEnum::BAR,
            'name' => 'Suco de laranja',
            'description' => 'Suco natural de laranja',
            'price' => 7.00,
            'favorite' => false,
            'active' => true,
        ]);

        //id:3 - category_id:7 - Agua mineral - Bar
        Product::factory()->create([
            'category_id' => 7,
            'production_local' => DepartamentEnum::BAR,
            'name' => 'Agua mineral sem gas',
            'description' => 'Agua mineral sem gas',
            'price' => 3.00,
            'favorite' => true,
            'active' => true,
        ]);

        //id:4 - category_id:7 - Agua com gas - Bar
        Product::factory()->create([
            'category_id' => 7,
            'production_local' => DepartamentEnum::BAR,
            'name' => 'Agua mineral com gas',
            'description' => 'Agua mineral com gas',
            'price' => 3.50,
            'favorite' => false,
            'active' => true,
        ]);

        //id:5 - category_id:3 - Batata frita tradicional - Kitchen
        Product::factory()->create([
            'category_id' => 3,
            'production_local' => DepartamentEnum::KITCHEN,
            'name' => 'Batata frita tradicional',
            'description' => 'Batata frita tradicional crocante',
            'price' => 15.00,
            'favorite' => true,
            'active' => true,
        ]);
        
        //id:6 - category_id:3 - Batata frita cheddar e bacon - Kitchen
        Product::factory()->create([
            'category_id' => 3,
            'production_local' => DepartamentEnum::KITCHEN,
            'name' => 'Batata frita cheddar e bacon',
            'description' => 'Batata frita com cheddar e bacon crocante',
            'price' => 20.00,
            'favorite' => false,
            'active' => true,
        ]);
        
    }
}
