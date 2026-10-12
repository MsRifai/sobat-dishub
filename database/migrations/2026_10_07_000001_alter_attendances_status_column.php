<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Drop PostgreSQL enum check constraint if exists
        DB::statement('ALTER TABLE attendances DROP CONSTRAINT IF EXISTS attendances_status_check;');

        Schema::table('attendances', function (Blueprint $table) {
            $table->string('status', 50)->default('tepat_waktu')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('attendances', function (Blueprint $table) {
            $table->string('status', 50)->default('tepat_waktu')->change();
        });
    }
};
