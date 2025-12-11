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
        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->integer('time_limit_pending')->nullable();
            $table->integer('time_limit_in_production')->nullable();
            $table->integer('time_limit_in_transit')->nullable();
            $table->integer('time_limit_closed')->nullable();
            $table->integer('time_limit_releasing')->nullable();
            $table->string('table_filter_mode')->default('AND');
            $table->json('table_filter_table')->nullable();
            $table->json('table_filter_check')->nullable();
            $table->json('table_filter_order')->nullable();
            $table->json('table_filter_departament')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('settings');
    }
};
