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
                'description' => 'Porções de alimentos e bebidas.'
            ],
        );

        //Id:2 - Bebidas
        Category::factory()->create(
            [
                'user_id' => 2,
                'category_id' => null,
                'name' => 'Bebidas',
                'description' => 'Bebidas alcoólicas e não alcoólicas.'
            ],
        );

        //Id:3 - Batata frita
        Category::factory()->create(
            [
                'user_id' => 2,
                'category_id' => 1,
                'name' => 'Batata frita',
                'description' => 'Bata frita'
            ],
        );

        //Id:4 - Peixe frito
        Category::factory()->create(
            [
                'user_id' => 2,
                'category_id' => 1,
                'name' => 'Peixe frito',
                'description' => 'Peixe frito de temporada'
            ],
        );

        //Id:5 - Calabresa
        Category::factory()->create(
            [
                'user_id' => 2,
                'category_id' => 1,
                'name' => 'Calabresa acebolada',
                'description' => 'Calabresa frita acebolada'
            ],
        );

        //Id:6 - Suco de abacaxi
        Category::factory()->create(
            [
                'user_id' => 2,
                'category_id' => 2,
                'name' => 'Suco de Abacaxi',
                'description' => 'Suco de poupa de abacaxi natural'
            ],
        );

        //Id:7 - Agua de coco
        Category::factory()->create(
            [
                'user_id' => 2,
                'category_id' => 2,
                'name' => 'Agua de coco',
                'description' => 'Agua de coco gelada'
            ],
        );
        /*
        for ($i = 0; $i < 30; $i++) {
            Category::factory()->create();
        }
        */
    }
}
