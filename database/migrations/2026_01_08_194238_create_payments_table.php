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
        Schema::create('payments', function (Blueprint $table) {
 $table->id();

            $table->foreignId('admin_id')
                ->constrained('users')
                ->cascadeOnDelete();

            $table->foreignId('table_id')
                ->nullable()
                ->constrained()
                ->nullOnDelete();

            $table->decimal('total_amount', 12, 2);
            $table->string('currency', 3)->default('BRL');

            $table->string('status'); 
            // pending | paid | failed | refunded | partial_refund

            $table->string('payment_method');
            // pix | credit_card | debit | cash

            $table->string('gateway')->nullable();
            $table->string('gateway_reference')->nullable();

            $table->timestamp('paid_at')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->index(['status', 'payment_method']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
