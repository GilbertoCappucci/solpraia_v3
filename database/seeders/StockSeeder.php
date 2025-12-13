<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Stock;

class StockSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //Coca Cola
        Stock::factory()->create([
            'product_id' => 1,
            'quantity' => -1,
            'price' => null,
        ]);

        //Suco de Laranja
        Stock::factory()->create([
            'product_id' => 2,
            'quantity' => 10,
            'price' => 100,
        ]);

        //Agua Mineral Sem Gas
        Stock::factory()->create([
            'product_id' => 3,
            'quantity' => -1,
            'price' => 100,
        ]);

        //Agua Mineral Com Gas
        Stock::factory()->create([
            'product_id' => 4,
            'quantity' => 20,
            'price' => null,
        ]);

        //Batata Frita Tradicional
        Stock::factory()->create([
            'product_id' => 5,
            'quantity' => 3,
            'price' => 100,
        ]);

        //Batata Frita Cheddar e Bacon
        Stock::factory()->create([
            'product_id' => 6,
            'quantity' => 1,
            'price' => 100,
        ]);
        
    }
}
