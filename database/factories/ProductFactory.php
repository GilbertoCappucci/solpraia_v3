<?php

namespace Database\Factories;

use App\Enums\RoleEnum;
use App\Models\Category;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Products>
 */
class ProductFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        //$user_id = $this->getRamdomUserId();

        return [
            //'category_id' => $this->getRandomCategoryId($user_id),
            'name' => fake()->word(),
            'description' => fake()->sentence(),
            'price' => fake()->randomFloat(2, 1, 100),
            'active' => fake()->boolean(80),
            'favorite' => fake()->boolean(20),
        ];
    }

    
    public function getRandomCategoryId(int $user_id): int
    {
        return Category::whereNull(['category_id'])
            ->where(['user_id' => $user_id, 'active' => true])
            ->inRandomOrder()
            ->first()->id;
    }
}
