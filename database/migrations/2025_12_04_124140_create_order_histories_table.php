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
        Schema::create('order_status_history', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained('orders')->onDelete('cascade');
            $table->string('from_status')->nullable(); // Status anterior
            $table->string('to_status'); // Novo status
            $table->double('price');
            $table->integer('quantity')->default(1);
            $table->timestamp('changed_at'); // Quando mudou
            $table->text('notes')->nullable(); // Observações opcionais
            $table->timestamps();
            
            $table->index(['order_id', 'changed_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_status_history');
    }
};
