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
        Schema::create('attendance_logs', function (Blueprint $table) {
            $table->id();
            $table->string('employee_code', 50);
            $table->string('department_snapshot', 100)->nullable();
            $table->string('employee_name_snapshot', 150)->nullable();
            $table->date('log_date');
            $table->time('log_time');
            $table->string('activity', 10);
            $table->string('punch_type', 20)->nullable();
            $table->text('image')->nullable();
            $table->text('address')->nullable();
            $table->timestamps();
            
            $table->foreign('employee_code')
                  ->references('employee_code')
                  ->on('employees')
                  ->cascadeOnDelete();
                  
            $table->unique(['employee_code', 'log_date', 'log_time', 'activity'], 'attendance_logs_unique');
            
            $table->index(['employee_code', 'log_date'], 'idx_attendance_logs_emp_date');
            $table->index(['employee_code', 'log_date', 'log_time'], 'idx_attendance_logs_emp_dt');
        });

        // Add check constraints using raw SQL
        DB::statement("ALTER TABLE attendance_logs ADD CONSTRAINT attendance_logs_activity_check CHECK (activity IN ('in', 'out'))");
        DB::statement("ALTER TABLE attendance_logs ADD CONSTRAINT attendance_logs_punch_type_check CHECK (punch_type IS NULL OR punch_type IN ('TIME_IN', 'BREAK_OUT', 'BREAK_IN', 'TIME_OUT'))");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attendance_logs');
    }
};