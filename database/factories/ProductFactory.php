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
        //$admin_id = $this->getRamdomUserId();

        return [
            //'category_id' => $this->getRandomCategoryId($admin_id),
            'name' => fake()->word(),
            'description' => fake()->sentence(),
            'active' => fake()->boolean(80),
            'favorite' => fake()->boolean(20),
        ];
    }

    
    public function getRandomCategoryId(int $admin_id): int
    {
        return Category::whereNull(['category_id'])
            ->where(['admin_id' => $admin_id, 'active' => true])
            ->inRandomOrder()
            ->first()->id;
    }
}
