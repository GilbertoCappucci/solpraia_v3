<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Device>
 */
class DeviceFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $deviceTypes = ['Tablet', 'Desktop', 'Smartphone', 'iPad', 'Computador', 'Terminal'];
        $locations = ['Cozinha', 'Caixa', 'Mesa', 'Bar', 'Entrada', 'Salão'];
        
        return [
            'nickname' => fake()->randomElement($deviceTypes) . ' ' . fake()->randomElement($locations) . ' ' . fake()->numberBetween(1, 10),
            'device_token' => \App\Models\Device::generateToken(),
            'device_fingerprint' => null, // Será gerado no primeiro uso
            'ip_address' => fake()->ipv4(),
            'active' => true,
            'expires_at' => now()->addYear(),
            'last_used_at' => null,
            'created_by' => 1, // Admin padrão
            'notes' => fake()->optional(0.3)->sentence(),
        ];
    }

    /**
     * Device inativo
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'active' => false,
        ]);
    }

    /**
     * Device expirado
     */
    public function expired(): static
    {
        return $this->state(fn (array $attributes) => [
            'expires_at' => now()->subDays(rand(1, 30)),
        ]);
    }

    /**
     * Device com fingerprint já registrado
     */
    public function withFingerprint(): static
    {
        return $this->state(fn (array $attributes) => [
            'device_fingerprint' => hash('sha256', fake()->uuid()),
        ]);
    }

    /**
     * Device usado recentemente
     */
    public function recentlyUsed(): static
    {
        return $this->state(fn (array $attributes) => [
            'last_used_at' => now()->subMinutes(rand(1, 60)),
        ]);
    }
}
