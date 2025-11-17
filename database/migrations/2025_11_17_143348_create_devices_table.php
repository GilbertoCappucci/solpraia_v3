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
        Schema::create('devices', function (Blueprint $table) {
            $table->id();
            $table->string('nickname')->comment('Nome amigável do device (ex: Tablet Cozinha)');
            $table->string('device_token', 64)->unique()->comment('Token de autorização');
            $table->string('device_fingerprint', 64)->nullable()->unique()->comment('Fingerprint único do device');
            $table->string('ip_address', 45)->nullable()->comment('IP do device');
            $table->boolean('active')->default(true)->comment('Device ativo/inativo');
            $table->timestamp('expires_at')->nullable()->comment('Data de expiração do acesso');
            $table->timestamp('last_used_at')->nullable()->comment('Último acesso');
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete()->comment('Admin que criou');
            $table->text('notes')->nullable()->comment('Observações sobre o device');
            $table->timestamps();
            $table->softDeletes();
            
            $table->index(['active', 'expires_at']);
            $table->index('last_used_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('devices');
    }
};
