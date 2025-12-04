<?php

namespace Database\Factories;

use App\Enums\CheckStatusEnum;
use App\Enums\OrderStatusEnum;
use App\Enums\RoleEnum;
use App\Models\Table;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

use function Symfony\Component\Clock\now;

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

        return [
            'status' => fake()->randomElement(OrderStatusEnum::cases())->value,
            'status_changed_at' => now(),
        ];
    }
}
