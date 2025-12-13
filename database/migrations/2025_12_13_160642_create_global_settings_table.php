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
        Schema::create('global_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('pix_key')->nullable();
            $table->string('pix_key_type')->nullable();
            $table->string('pix_name')->nullable();
            $table->string('pix_city')->nullable();
            $table->integer('time_limit_pending')->nullable();
            $table->integer('time_limit_in_production')->nullable();
            $table->integer('time_limit_in_transit')->nullable();
            $table->integer('time_limit_closed')->nullable();
            $table->integer('time_limit_releasing')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('global_settings');
    }
};
