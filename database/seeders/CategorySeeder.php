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
                'admin_id' => 1,
                'category_id' => null,
                'name' => 'Porções',
                'active' => true,
                'description' => 'Porções de alimentos e bebidas.'
            ],
        );

        //Id:2 - Bebidas
        Category::factory()->create(
            [
                'admin_id' => 1,
                'category_id' => null,
                'name' => 'Bebidas',
                'active' => true,
                'description' => 'Bebidas alcoólicas e não alcoólicas.'
            ],
        );

        //Id:3 - Batata frita
        Category::factory()->create(
            [
                'admin_id' => 1,
                'category_id' => 1,
                'name' => 'Batata frita',
                'active' => true,
                'description' => 'Bata frita'
            ],
        );

        //Id:4 - Calabresa
        Category::factory()->create(
            [
                'admin_id' => 1,
                'category_id' => 1,
                'name' => 'Calabresa acebolada',
                'active' => true,
                'description' => 'Calabresa frita acebolada'
            ],
        );

        //Id:5 - Suco de fruta
        Category::factory()->create(
            [
                'admin_id' => 1,
                'category_id' => 2,
                'name' => 'Suco de Fruta',
                'active' => true,
                'description' => 'Suco de poupa de fruta natural'
            ],
        );

        //Id:6 - Agua
        Category::factory()->create(
            [
                'admin_id' => 1,
                'category_id' => 2,
                'name' => 'Agua',
                'active' => true,
                'description' => 'Agua natural mineral'
            ],
        );

        //Id:7 - Refrigerante
        Category::factory()->create(
            [
                'admin_id' => 1,
                'category_id' => 2,
                'name' => 'Refrigerante',
                'active' => true,
                'description' => 'Refrigerante sabor cola'
            ],
        );

       
        /*
        for ($i = 0; $i < 30; $i++) {
            Category::factory()->create();
        }
        */
    }
}
