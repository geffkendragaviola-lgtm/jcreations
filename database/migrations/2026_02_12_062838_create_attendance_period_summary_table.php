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
        Schema::create('attendance_period_summary', function (Blueprint $table) {
            $table->id();
            $table->string('employee_code', 50);
            $table->date('period_start');
            $table->date('period_end');
            $table->integer('late_frequency')->default(0);
            $table->integer('missed_logs_count')->default(0);
            $table->integer('grace_days')->default(0);
            $table->integer('absences')->default(0);
            $table->integer('days_worked')->default(0);
            $table->integer('late_duration')->default(0);
            $table->decimal('avg_late_per_occurrence', 6, 2)->default(0);
            $table->integer('total_undertime')->default(0);
            $table->integer('undertime_frequency')->default(0);
            $table->time('most_frequent_late_time')->nullable();
            $table->boolean('letter_generated')->default(false);
            $table->text('letter_reference')->nullable();
            $table->timestamps();
            
            $table->foreign('employee_code')
                  ->references('employee_code')
                  ->on('employees')
                  ->cascadeOnDelete();
                  
            $table->unique(['employee_code', 'period_start', 'period_end'], 'attendance_period_summary_unique');
            $table->index(['employee_code', 'period_start', 'period_end'], 'idx_attendance_period_summary_emp_period');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attendance_period_summary');
    }
};