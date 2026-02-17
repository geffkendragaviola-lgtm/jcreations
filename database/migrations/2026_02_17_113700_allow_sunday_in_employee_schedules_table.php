<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $driver = DB::getDriverName();

        // Drop existing check constraint, then recreate it including Sunday.
        try {
            if ($driver === 'mysql') {
                DB::statement("ALTER TABLE employee_schedules DROP CHECK employee_schedules_day_of_week_check");
            } else {
                DB::statement("ALTER TABLE employee_schedules DROP CONSTRAINT employee_schedules_day_of_week_check");
            }
        } catch (\Throwable $e) {
            // ignore (e.g. constraint doesn't exist / driver doesn't support)
        }

        try {
            DB::statement("ALTER TABLE employee_schedules ADD CONSTRAINT employee_schedules_day_of_week_check CHECK (day_of_week IN ('Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'))");
        } catch (\Throwable $e) {
            // ignore
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $driver = DB::getDriverName();

        try {
            if ($driver === 'mysql') {
                DB::statement("ALTER TABLE employee_schedules DROP CHECK employee_schedules_day_of_week_check");
            } else {
                DB::statement("ALTER TABLE employee_schedules DROP CONSTRAINT employee_schedules_day_of_week_check");
            }
        } catch (\Throwable $e) {
            // ignore
        }

        try {
            DB::statement("ALTER TABLE employee_schedules ADD CONSTRAINT employee_schedules_day_of_week_check CHECK (day_of_week IN ('Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'))");
        } catch (\Throwable $e) {
            // ignore
        }
    }
};
