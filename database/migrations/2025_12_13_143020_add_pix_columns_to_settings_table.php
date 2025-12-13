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
        Schema::table('settings', function (Blueprint $table) {
            $table->string('pix_key')->nullable()->after('table_filter_departament');
            $table->string('pix_key_type')->nullable()->after('pix_key');
            $table->string('pix_name')->nullable()->after('pix_key_type');
            $table->string('pix_city')->nullable()->after('pix_name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            $table->dropColumn(['pix_key', 'pix_key_type', 'pix_name', 'pix_city']);
        });
    }
};
