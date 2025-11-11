<?php

namespace Database\Factories;

use App\Models\ProductCategory;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ProductCategory>
 */
class ProductCategoryFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::inRandomOrder()->first()->id,
            'parent_id' => fake()->boolean(20) ? null : ProductCategory::inRandomOrder()->first()?->id,
            'name' => fake()->word(),
            'description' => fake()->sentence(),
            'active' => fake()->boolean(80),
        ];
    }
}
