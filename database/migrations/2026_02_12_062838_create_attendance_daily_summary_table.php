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
        Schema::create('attendance_daily_summary', function (Blueprint $table) {
            $table->id();
            $table->string('employee_code', 50);
            $table->date('summary_date');
            $table->time('time_in')->nullable();
            $table->time('break_out')->nullable();
            $table->time('break_in')->nullable();
            $table->time('time_out')->nullable();
            $table->boolean('grace_used')->default(false);
            $table->integer('late_in_minutes')->default(0);
            $table->integer('undertime_break_out_minutes')->default(0);
            $table->integer('late_break_in_minutes')->default(0);
            $table->integer('ot_minutes')->default(0);
            $table->decimal('total_hours', 6, 2)->default(0);
            $table->integer('missed_logs')->default(0);
            $table->string('status', 20)->default('ON_TIME');
            $table->timestamps();
            
            $table->foreign('employee_code')
                  ->references('employee_code')
                  ->on('employees')
                  ->cascadeOnDelete();
                  
            $table->unique(['employee_code', 'summary_date'], 'attendance_daily_summary_unique');
            
            $table->index('summary_date', 'idx_attendance_daily_summary_date');
            $table->index(['employee_code', 'summary_date'], 'idx_attendance_daily_summary_emp_date');
        });

        // Add check constraint using raw SQL
        DB::statement("ALTER TABLE attendance_daily_summary ADD CONSTRAINT attendance_daily_summary_status_check CHECK (status IN ('ON_TIME', 'LATE', 'UNDERTIME', 'MISSED_LOG', 'ABSENT'))");
        
        // For generated columns - if using PostgreSQL
        if (DB::connection()->getDriverName() === 'pgsql') {
            DB::statement("ALTER TABLE attendance_daily_summary ADD COLUMN total_late_minutes INTEGER GENERATED ALWAYS AS (late_in_minutes + late_break_in_minutes) STORED");
            DB::statement("ALTER TABLE attendance_daily_summary ADD COLUMN undertime_minutes INTEGER GENERATED ALWAYS AS (undertime_break_out_minutes) STORED");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attendance_daily_summary');
    }
};