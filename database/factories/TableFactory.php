<?php

namespace Database\Factories;

use App\Enums\RoleEnum;
use App\Enums\TableStatusEnum;
use App\Models\Table;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Table>
 */
class TableFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $adminId = User::where(['role' => RoleEnum::ADMIN->value, 'active' => true])
            ->inRandomOrder()
            ->first()
            ->id;
        return [
            'admin_id' => $adminId,
            'name' => null,
            'number' => fake()->unique()->numberBetween(1, 10),
            'status' => $this->faker->randomElement(array_column(TableStatusEnum::cases(), 'value')),
        ];
    }

}
