<?php

namespace Database\Seeders;

use App\Models\AuthorizedDevice;
use App\Models\User;
use App\Enums\RoleEnum;
use Illuminate\Database\Seeder;

class AuthorizedDeviceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Garantir que existe pelo menos um admin
        $admin = User::where('role', RoleEnum::ADMIN->value)->first();
        
        if (!$admin) {
            $admin = User::factory()->create([
                'role' => RoleEnum::ADMIN->value,
                'name' => 'Admin Principal',
                'email' => 'admin@restaurant.com',
            ]);
        }

        // Dispositivos principais ativos
        AuthorizedDevice::factory()
            ->permanent()
            ->create([
                'device_name' => 'Desktop Administrativo Principal',
                'device_token' => 'DEV_ADMIN_DESKTOP_MAIN_001',
                'notes' => 'Dispositivo principal da administração - nunca expira',
                'created_by' => $admin->id,
                'max_sessions' => 3,
                'usage_count' => 1250,
                'last_used_at' => now()->subHours(2),
            ]);

        AuthorizedDevice::factory()
            ->create([
                'device_name' => 'Tablet Cozinha - Pedidos',
                'device_token' => 'DEV_KITCHEN_TABLET_001',
                'expires_at' => now()->addMonths(6),
                'notes' => 'Tablet da cozinha para visualização de pedidos',
                'created_by' => $admin->id,
                'ip_restriction' => true,
                'allowed_ips' => ['192.168.1.100', '192.168.1.101'],
                'usage_count' => 890,
            ]);

        AuthorizedDevice::factory()
            ->create([
                'device_name' => 'Terminal Caixa - Pagamentos',
                'device_token' => 'DEV_CASHIER_TERMINAL_001', 
                'expires_at' => now()->addYear(),
                'notes' => 'Terminal dedicado para operações de caixa',
                'created_by' => $admin->id,
                'max_sessions' => 1,
                'ip_restriction' => true,
                'allowed_ips' => ['192.168.1.50'],
                'usage_count' => 2340,
            ]);

        // Dispositivos móveis para garçons
        for ($i = 1; $i <= 3; $i++) {
            AuthorizedDevice::factory()
                ->create([
                    'device_name' => "Smartphone Garçom {$i}",
                    'device_token' => "DEV_WAITER_MOBILE_00{$i}",
                    'expires_at' => now()->addMonths(3),
                    'notes' => "Dispositivo móvel do garçom {$i}",
                    'created_by' => $admin->id,
                    'max_sessions' => 1,
                    'usage_count' => fake()->numberBetween(200, 800),
                ]);
        }

        // Dispositivos variados com diferentes estados
        AuthorizedDevice::factory(5)->create(['created_by' => $admin->id]);
        
        // Dispositivos com restrição de IP
        AuthorizedDevice::factory(3)
            ->ipRestricted()
            ->create(['created_by' => $admin->id]);

        // Dispositivos expirados (para testar)
        AuthorizedDevice::factory(2)
            ->expired()
            ->create([
                'created_by' => $admin->id,
                'notes' => 'Dispositivo expirado - aguardando renovação',
            ]);

        // Dispositivos inativos
        AuthorizedDevice::factory(2)
            ->inactive()
            ->create(['created_by' => $admin->id]);

        // Dispositivos com uso intenso
        AuthorizedDevice::factory(1)
            ->heavyUsage()
            ->create([
                'device_name' => 'Desktop Gerência - Relatórios',
                'device_token' => 'DEV_MANAGER_DESKTOP_001',
                'created_by' => $admin->id,
                'notes' => 'Dispositivo da gerência com uso intenso',
            ]);

        $this->command->info('✓ Dispositivos autorizados criados com sucesso!');
        $this->command->info('✓ Total: ' . AuthorizedDevice::count() . ' dispositivos');
        $this->command->info('✓ Ativos: ' . AuthorizedDevice::where('is_active', true)->count());
        $this->command->info('✓ Com restrição IP: ' . AuthorizedDevice::where('ip_restriction', true)->count());
    }
}