<?php

namespace Database\Factories;

use App\Models\AuthorizedDevice;
use App\Models\User;
use App\Enums\RoleEnum;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\AuthorizedDevice>
 */
class AuthorizedDeviceFactory extends Factory
{
    protected $model = AuthorizedDevice::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $deviceTypes = [
            'Desktop - Administrativo',
            'Laptop - Gerência', 
            'Tablet - Cozinha',
            'Terminal - Caixa',
            'Smartphone - Garçom',
            'Desktop - Recepção'
        ];

        $browsers = [
            'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
            'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
            'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
            'Mozilla/5.0 (iPhone; CPU iPhone OS 17_0 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/17.0 Mobile/15E148 Safari/604.1'
        ];

        // Gerar informações do dispositivo
        $deviceInfo = [
            'screen_resolution' => fake()->randomElement(['1920x1080', '1366x768', '2560x1440', '1440x900']),
            'timezone' => fake()->timezone(),
            'language' => fake()->randomElement(['pt-BR', 'en-US', 'es-ES']),
            'platform' => fake()->randomElement(['Windows', 'macOS', 'Linux', 'iOS', 'Android']),
            'browser' => fake()->randomElement(['Chrome', 'Firefox', 'Safari', 'Edge']),
            'registered_at' => now()->format('Y-m-d H:i:s')
        ];

        return [
            'device_name' => fake()->randomElement($deviceTypes),
            'device_token' => AuthorizedDevice::generateToken(),
            'device_fingerprint' => 'fp_' . Str::random(40),
            'registered_ip' => fake()->ipv4(),
            'user_agent' => fake()->randomElement($browsers),
            'device_info' => $deviceInfo,
            'expires_at' => fake()->optional(0.7)->dateTimeBetween('now', '+1 year'), // 70% têm expiração
            'max_sessions' => fake()->numberBetween(1, 3),
            'ip_restriction' => fake()->boolean(30), // 30% têm restrição de IP
            'allowed_ips' => function (array $attributes) {
                return $attributes['ip_restriction'] 
                    ? [
                        $attributes['registered_ip'],
                        fake()->ipv4(),
                        fake()->optional()->ipv4()
                    ] 
                    : null;
            },
            'last_used_at' => fake()->optional(0.8)->dateTimeBetween('-30 days', 'now'),
            'last_ip' => function (array $attributes) {
                return $attributes['last_used_at'] ? fake()->ipv4() : null;
            },
            'usage_count' => fake()->numberBetween(0, 500),
            'is_active' => fake()->boolean(90), // 90% ativos
            'created_by' => function () {
                return User::where('role', RoleEnum::ADMIN->value)->inRandomOrder()->first()?->id 
                    ?? User::factory()->create(['role' => RoleEnum::ADMIN->value])->id;
            },
            'updated_by' => fake()->optional(0.3)->randomElement(
                User::where('role', RoleEnum::ADMIN->value)->pluck('id')->toArray()
            ),
            'notes' => fake()->optional(0.4)->sentence()
        ];
    }

    /**
     * Indica que o dispositivo está expirado
     */
    public function expired(): static
    {
        return $this->state(fn (array $attributes) => [
            'expires_at' => fake()->dateTimeBetween('-6 months', '-1 day'),
            'is_active' => false,
        ]);
    }

    /**
     * Indica que o dispositivo nunca expira
     */
    public function permanent(): static
    {
        return $this->state(fn (array $attributes) => [
            'expires_at' => null,
        ]);
    }

    /**
     * Dispositivo com restrição de IP
     */
    public function ipRestricted(): static
    {
        return $this->state(fn (array $attributes) => [
            'ip_restriction' => true,
            'allowed_ips' => [
                fake()->ipv4(),
                fake()->ipv4(),
            ],
        ]);
    }

    /**
     * Dispositivo muito usado
     */
    public function heavyUsage(): static
    {
        return $this->state(fn (array $attributes) => [
            'usage_count' => fake()->numberBetween(1000, 5000),
            'last_used_at' => fake()->dateTimeBetween('-2 days', 'now'),
        ]);
    }

    /**
     * Dispositivo inativo
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
            'notes' => 'Dispositivo desativado pelo administrador',
        ]);
    }
}