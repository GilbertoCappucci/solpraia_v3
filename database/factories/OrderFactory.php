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
        // Orders não têm mais price, quantity ou status diretamente
        // Esses valores vêm do histórico
        return [
            // Apenas relacionamentos básicos
        ];
    }

    /**
     * Callback executado após criar o pedido
     * Cria o histórico inicial com status PENDING
     */
    public function configure()
    {
        return $this->afterCreating(function (\App\Models\Order $order) {
            // Cria histórico inicial
            $order->statusHistory()->create([
                'from_status' => null,
                'to_status' => OrderStatusEnum::PENDING->value,
                'price' => fake()->randomFloat(2, 5, 50), // Preço aleatório para testes
                'quantity' => 1,
                'changed_at' => now(),
            ]);
        });
    }
}
