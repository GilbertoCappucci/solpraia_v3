<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Category;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {

        //Id:1 - Porcoes
        Category::factory()->create(
            [
                'user_id' => 2,
                'category_id' => null,
                'name' => 'Porções',
                'active' => true,
                'description' => 'Porções de alimentos e bebidas.'
            ],
        );

        //Id:2 - Bebidas
        Category::factory()->create(
            [
                'user_id' => 2,
                'category_id' => null,
                'name' => 'Bebidas',
                'active' => true,
                'description' => 'Bebidas alcoólicas e não alcoólicas.'
            ],
        );

        //Id:3 - Batata frita
        Category::factory()->create(
            [
                'user_id' => 2,
                'category_id' => 1,
                'name' => 'Batata frita',
                'active' => true,
                'description' => 'Bata frita'
            ],
        );

        //Id:4 - Peixe frito
        Category::factory()->create(
            [
                'user_id' => 2,
                'category_id' => 1,
                'name' => 'Peixe frito',
                'active' => true,
                'description' => 'Peixe frito de temporada'
            ],
        );

        //Id:5 - Calabresa
        Category::factory()->create(
            [
                'user_id' => 2,
                'category_id' => 1,
                'name' => 'Calabresa acebolada',
                'active' => true,
                'description' => 'Calabresa frita acebolada'
            ],
        );

        //Id:6 - Suco de fruta
        Category::factory()->create(
            [
                'user_id' => 2,
                'category_id' => 2,
                'name' => 'Suco de Fruta',
                'active' => true,
                'description' => 'Suco de poupa de fruta natural'
            ],
        );

        //Id:7 - Agua
        Category::factory()->create(
            [
                'user_id' => 2,
                'category_id' => 2,
                'name' => 'Agua',
                'active' => true,
                'description' => 'Agua natural mineral'
            ],
        );

        //Id:8 - Refrigerante
        Category::factory()->create(
            [
                'user_id' => 2,
                'category_id' => 2,
                'name' => 'Refrigerante',
                'active' => true,
                'description' => 'Refrigerante sabor cola'
            ],
        );

        //Id:9 - Porções
        Category::factory()->create(
            [
                'user_id' => 5,
                'category_id' => null,
                'name' => 'Porções',
                'active' => true,
                'description' => 'Porções de alimentos'
            ],
        );

        //Id:10 - Bebidas
        Category::factory()->create(
            [
                'user_id' => 5,
                'category_id' => null,
                'name' => 'Bebidas',
                'active' => true,
                'description' => 'Bebidas alcoólicas e não alcoólicas'
            ],
        );
        
        //Id:11 - Batata fritas
        Category::factory()->create(
            [
                'user_id' => 5,
                'category_id' => 9,
                'name' => 'Batata fritas',
                'active' => true,
                'description' => 'Batata fritas'
            ],
        );

        //Id:12 - Bebida não alcoólica
        Category::factory()->create(
            [
                'user_id' => 5,
                'category_id' => 10,
                'name' => 'Bebida não alcoólica',
                'active' => true,
                'description' => 'Bebidas não alcoólicas'
            ],
        );

        /*
        for ($i = 0; $i < 30; $i++) {
            Category::factory()->create();
        }
        */
    }
}
