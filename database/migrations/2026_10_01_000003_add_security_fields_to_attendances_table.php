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
        Schema::table('attendances', function (Blueprint $table) {
            $table->boolean('is_mock_location')->default(false)->after('late_minutes');
            $table->float('gps_accuracy')->nullable()->after('is_mock_location');
            $table->boolean('is_suspicious')->default(false)->after('gps_accuracy');
            $table->string('security_note')->nullable()->after('is_suspicious');
            $table->string('ip_address')->nullable()->after('security_note');
            $table->string('user_agent')->nullable()->after('ip_address');
            $table->string('status', 50)->default('tepat_waktu')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('attendances', function (Blueprint $table) {
            $table->dropColumn([
                'is_mock_location',
                'gps_accuracy',
                'is_suspicious',
                'security_note',
                'ip_address',
                'user_agent',
            ]);
        });
    }
};
