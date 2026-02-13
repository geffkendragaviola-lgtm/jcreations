<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

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
        DB::statement("DELETE FROM attendance_logs a USING attendance_logs b WHERE a.id > b.id AND a.employee_code = b.employee_code AND a.log_date = b.log_date AND a.log_time = b.log_time AND a.activity = b.activity");
        DB::statement("DELETE FROM attendance_daily_summary a USING attendance_daily_summary b WHERE a.id > b.id AND a.employee_code = b.employee_code AND a.summary_date = b.summary_date");
        DB::statement("DELETE FROM attendance_period_summary a USING attendance_period_summary b WHERE a.id > b.id AND a.employee_code = b.employee_code AND a.period_start = b.period_start AND a.period_end = b.period_end");

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
