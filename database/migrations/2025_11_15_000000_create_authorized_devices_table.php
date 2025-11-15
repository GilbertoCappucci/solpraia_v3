<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('authorized_devices', function (Blueprint $table) {
            $table->id();
            $table->string('device_name'); // Nome/descrição do dispositivo
            $table->string('device_token')->unique(); // Token único do dispositivo
            $table->string('device_fingerprint')->nullable(); // Hash do fingerprint do dispositivo
            $table->ipAddress('registered_ip')->nullable(); // IP de registro
            $table->text('user_agent')->nullable(); // User agent de registro
            $table->json('device_info')->nullable(); // Informações extras do dispositivo
            
            // Configurações do token
            $table->timestamp('expires_at')->nullable(); // Data de expiração
            $table->integer('max_sessions')->default(1); // Máximo de sessões simultâneas
            $table->boolean('ip_restriction')->default(false); // Restringir por IP
            $table->json('allowed_ips')->nullable(); // IPs permitidos (se ip_restriction = true)
            
            // Controle de uso
            $table->timestamp('last_used_at')->nullable(); // Último uso
            $table->ipAddress('last_ip')->nullable(); // Último IP usado
            $table->integer('usage_count')->default(0); // Contador de uso
            $table->boolean('is_active')->default(true); // Status ativo/inativo
            
            // Auditoria
            $table->foreignId('created_by')->constrained('users'); // Admin que criou
            $table->foreignId('updated_by')->nullable()->constrained('users'); // Admin que atualizou
            $table->text('notes')->nullable(); // Observações do admin
            
            $table->timestamps();
            
            // Índices
            $table->index(['device_token', 'is_active']);
            $table->index(['expires_at', 'is_active']);
            $table->index('last_used_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('authorized_devices');
    }
};