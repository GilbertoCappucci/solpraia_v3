<?php

namespace Database\Factories;

use App\Enums\RoleEnum;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Menu>
 */
class MenuFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {

        $userId = User::where('role', RoleEnum::ADMIN->value)
            ->where('active', true)
            ->inRandomOrder()
            ->first()->id;

        return [
            'user_id' => $userId,
            'name' => $this->faker->word(),
            'is_active' => ($this->hasMenus($userId)) ? false : true,
        ];
    }

    public function hasMenus(int $userId): bool
    {
        return User::find($userId)->menus()->exists();
    }
}
