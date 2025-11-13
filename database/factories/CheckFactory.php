<?php

namespace Database\Factories;

use App\Models\Table;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Check>
 */
class CheckFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {

        $table_id = Table::inRandomOrder()->first()->id;

        return [
            'table_id' => $table_id,
            'total' => fake()->randomFloat(2, 10, 500),
            'status' => 'OPEN',
            'opened_at' => now(),
            'closed_at' => null,
        ];
    }
}
