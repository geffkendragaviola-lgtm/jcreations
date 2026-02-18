<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Throwable;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $driver = DB::connection()->getDriverName();

        if ($driver === 'pgsql') {
            DB::statement("ALTER TABLE attendance_daily_summary DROP CONSTRAINT IF EXISTS attendance_daily_summary_status_check");
        } elseif ($driver === 'mysql') {
            try {
                DB::statement("ALTER TABLE attendance_daily_summary DROP CHECK attendance_daily_summary_status_check");
            } catch (Throwable $e) {
                // ignore
            }
        }

        if ($driver === 'pgsql') {
            DB::statement("ALTER TABLE attendance_daily_summary ALTER COLUMN status TYPE VARCHAR(50)");
            DB::statement("ALTER TABLE attendance_daily_summary ALTER COLUMN status SET DEFAULT 'Ontime'");
        } elseif ($driver === 'mysql') {
            DB::statement("ALTER TABLE attendance_daily_summary MODIFY status VARCHAR(50) NOT NULL DEFAULT 'Ontime'");
        }

        DB::table('attendance_daily_summary')
            ->whereIn('status', ['ON_TIME', 'LATE', 'UNDERTIME', 'MISSED_LOG', 'ABSENT'])
            ->update([
                'status' => DB::raw("CASE status
                    WHEN 'ON_TIME' THEN 'Ontime'
                    WHEN 'LATE' THEN 'Late'
                    WHEN 'UNDERTIME' THEN 'Undertime'
                    WHEN 'MISSED_LOG' THEN 'Incomplete Logs'
                    WHEN 'ABSENT' THEN 'Whole Day Absent'
                    ELSE status
                END"),
            ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('attendance_daily_summary')
            ->whereIn('status', ['Ontime', 'Late', 'Undertime', 'Incomplete Logs', 'Whole Day Absent'])
            ->update([
                'status' => DB::raw("CASE status
                    WHEN 'Ontime' THEN 'ON_TIME'
                    WHEN 'Late' THEN 'LATE'
                    WHEN 'Undertime' THEN 'UNDERTIME'
                    WHEN 'Incomplete Logs' THEN 'MISSED_LOG'
                    WHEN 'Whole Day Absent' THEN 'ABSENT'
                    ELSE status
                END"),
            ]);

        if ($driver === 'pgsql') {
            DB::statement("ALTER TABLE attendance_daily_summary ALTER COLUMN status TYPE VARCHAR(20)");
            DB::statement("ALTER TABLE attendance_daily_summary ALTER COLUMN status SET DEFAULT 'ON_TIME'");
        } elseif ($driver === 'mysql') {
            DB::statement("ALTER TABLE attendance_daily_summary MODIFY status VARCHAR(20) NOT NULL DEFAULT 'ON_TIME'");
        }

        if ($driver === 'pgsql') {
            DB::statement("ALTER TABLE attendance_daily_summary ADD CONSTRAINT attendance_daily_summary_status_check CHECK (status IN ('ON_TIME', 'LATE', 'UNDERTIME', 'MISSED_LOG', 'ABSENT'))");
        }
    }
};
