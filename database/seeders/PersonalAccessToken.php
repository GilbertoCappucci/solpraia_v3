<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PersonalAccessToken extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('🔑 Gerando tokens de device para employees...');

        // Buscar todos os employees (não-admins)
        $employees = User::where('role', '!=', 'admin')->get();

        foreach ($employees as $employee) {
            // Gerar 1-2 tokens por employee (simulando múltiplos devices)
            $devicesCount = rand(1, 2);

            for ($i = 1; $i <= $devicesCount; $i++) {
                $deviceTypes = ['Desktop', 'Mobile', 'Tablet'];
                $deviceType = $deviceTypes[array_rand($deviceTypes)];
                
                $deviceName = "{$deviceType} - {$employee->name} #{$i}";
                
                // Gerar token com expiração de 1 ano
                $token = $employee->generateDeviceToken($deviceName, 365);
                
                $this->command->info("  ✅ Token para {$employee->name}: {$token}");
                $this->command->info("     Device: {$deviceName}");
                $this->command->info("     Expira em: " . now()->addYear()->format('d/m/Y'));
                $this->command->newLine();
            }
        }

        // Criar um token especial para testes (token conhecido)
        $testEmployee = $employees->first();
        if ($testEmployee) {
            $this->command->info('🧪 Criando token de TESTE...');
            $testToken = $testEmployee->generateDeviceToken('TEST Device - Desktop', 365);
            $this->command->warn("  🔐 TOKEN DE TESTE: {$testToken}");
            $this->command->warn("  Use este token para testar no navegador!");
            $this->command->newLine();
        }

        $this->command->info('✅ Tokens de device gerados com sucesso!');
    }
}
