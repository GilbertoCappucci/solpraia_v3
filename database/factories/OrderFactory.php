<?php

namespace Database\Factories;

use App\Enums\OrderStatusEnum;
use App\Enums\RoleEnum;
use App\Models\Table;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Order>
 */
class OrderFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {

        $user = User::where(['role' => RoleEnum::ADMIN->value, 'active' => true])->inRandomOrder()->first();

        $Table = $user->Tables()->where('active', true)->inRandomOrder()->first();

        $employee = $user->employees()->where('active', true)->inRandomOrder()->first();

        $product = $user->products()->where('active', true)->inRandomOrder()->first();

        return [
            'employee_id' => $employee->id,
            'beach_umbrella_id' => $Table->id,
            'product_id' => $product->id,
            'quantity' => fake()->numberBetween(1, 5),
            'status' => fake()->randomElement(OrderStatusEnum::cases())->value,
        ];
    }
}
