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
        Schema::table('office_locations', function (Blueprint $table) {
            $table->time('check_in_cutoff')->default('07:30:00')->after('radius_meters');
            $table->time('check_out_start_weekday')->default('16:00:00')->after('check_in_cutoff');
            $table->time('check_out_start_friday')->default('15:30:00')->after('check_out_start_weekday');
            $table->time('check_out_start_weekend')->default('16:00:00')->after('check_out_start_friday');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('office_locations', function (Blueprint $table) {
            $table->dropColumn([
                'check_in_cutoff',
                'check_out_start_weekday',
                'check_out_start_friday',
                'check_out_start_weekend',
            ]);
        });
    }
};
