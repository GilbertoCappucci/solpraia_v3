<?php

namespace Database\Seeders;

use App\Models\Device;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DeviceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('📱 Criando devices autorizados...');

        // Device 1: Tablet Caixa Principal
        $device1 = Device::create([
            'nickname' => 'Tablet Caixa Principal',
            'device_token' => Device::generateToken(),
            'active' => true,
            'expires_at' => now()->addYear(),
            'created_by' => 1,
            'notes' => 'Tablet usado no caixa principal do restaurante',
        ]);

        $this->command->info("  ✅ {$device1->nickname}");
        $this->command->warn("     Token: {$device1->device_token}");
        $this->command->newLine();

        // Device 2: iPad Cozinha
        $device2 = Device::create([
            'nickname' => 'iPad Cozinha',
            'device_token' => Device::generateToken(),
            'active' => true,
            'expires_at' => now()->addYear(),
            'created_by' => 1,
            'notes' => 'iPad para visualização de pedidos na cozinha',
        ]);

        $this->command->info("  ✅ {$device2->nickname}");
        $this->command->warn("     Token: {$device2->device_token}");
        $this->command->newLine();

        // Device 3: Desktop Gerência
        $device3 = Device::create([
            'nickname' => 'Desktop Gerência',
            'device_token' => Device::generateToken(),
            'active' => true,
            'expires_at' => now()->addYear(),
            'created_by' => 1,
            'notes' => 'Computador da sala de gerência',
        ]);

        $this->command->info("  ✅ {$device3->nickname}");
        $this->command->warn("     Token: {$device3->device_token}");
        $this->command->newLine();

        // Token de TESTE fixo para desenvolvimento
        $testDevice = Device::create([
            'nickname' => 'TEST Device - Desenvolvimento',
            'device_token' => 'DEV_TEST_TOKEN_123456789',
            'active' => true,
            'expires_at' => now()->addYears(10),
            'created_by' => 1,
            'notes' => 'Device de teste para desenvolvimento - NÃO USAR EM PRODUÇÃO',
        ]);

        $this->command->alert('🧪 DEVICE DE TESTE CRIADO');
        $this->command->warn("Token: {$testDevice->device_token}");
        $this->command->warn('Use este token para testar no navegador!');
        $this->command->newLine();

        $this->command->info('✅ Total de devices criados: 4');
    }
}
