<?php

use App\Enums\CheckStatusEnum;
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
        Schema::create('check_status_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('check_id')->constrained('checks')->cascadeOnDelete();
            $table->enum('status', CheckStatusEnum::cases());
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('check_status_histories');
    }
};
