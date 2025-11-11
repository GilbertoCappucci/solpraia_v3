<?php

namespace Database\Factories;

use App\Enums\RoleEnum;
use App\Models\Category;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Category>
 */
class CategoryFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {

        $user_id = $this->getRamdomUserId();

        return [
            'user_id' => $user_id,
            'category_id' => fake()->boolean(10) ? null : $this->getRandomCategoryId($user_id),
            'name' => fake()->word(),
            'description' => fake()->sentence(),
            'active' => fake()->boolean(80),
        ];
    }

    public function getRamdomUserId(): int
    {
        return User::where(['role' => RoleEnum::ADMIN->value])
            ->inRandomOrder()
            ->first()->id;
    }

    public function getRandomCategoryId(int $user_id): ?int
    {
        return Category::whereNull(['category_id'])
            ->where(['user_id' => $user_id])
            ->inRandomOrder()
            ->first()?->id;
    }
}
