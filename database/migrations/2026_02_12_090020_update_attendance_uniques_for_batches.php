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
        Schema::table('attendance_logs', function (Blueprint $table) {
            $table->dropUnique('attendance_logs_unique');
            $table->unique(['import_batch_id', 'employee_code', 'log_date', 'log_time', 'activity'], 'attendance_logs_unique');
        });

        Schema::table('attendance_daily_summary', function (Blueprint $table) {
            $table->dropUnique('attendance_daily_summary_unique');
            $table->unique(['import_batch_id', 'employee_code', 'summary_date'], 'attendance_daily_summary_unique');
        });

        Schema::table('attendance_period_summary', function (Blueprint $table) {
            $table->dropUnique('attendance_period_summary_unique');
            $table->unique(['import_batch_id', 'employee_code', 'period_start', 'period_end'], 'attendance_period_summary_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('attendance_logs', function (Blueprint $table) {
            $table->dropUnique('attendance_logs_unique');
            $table->unique(['employee_code', 'log_date', 'log_time', 'activity'], 'attendance_logs_unique');
        });

        Schema::table('attendance_daily_summary', function (Blueprint $table) {
            $table->dropUnique('attendance_daily_summary_unique');
            $table->unique(['employee_code', 'summary_date'], 'attendance_daily_summary_unique');
        });

        Schema::table('attendance_period_summary', function (Blueprint $table) {
            $table->dropUnique('attendance_period_summary_unique');
            $table->unique(['employee_code', 'period_start', 'period_end'], 'attendance_period_summary_unique');
        });
    }
};
