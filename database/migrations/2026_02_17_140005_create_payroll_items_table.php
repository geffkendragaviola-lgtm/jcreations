<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payroll_items', function (Blueprint $table) {
            $table->unsignedBigInteger('id')->primary();
            $table->unsignedBigInteger('payroll_run_id');
            $table->unsignedBigInteger('employee_id');
            $table->string('employee_code', 50)->nullable();
            $table->decimal('daily_rate', 12, 2)->default(0);
            $table->decimal('hourly_rate', 12, 4)->default(0);
            $table->integer('regular_worked_days')->default(0);
            $table->integer('rest_day_worked_days')->default(0);
            $table->decimal('paid_leave_days', 6, 2)->default(0);
            $table->decimal('unpaid_leave_days', 6, 2)->default(0);
            $table->integer('unpaid_absence_days')->default(0);
            $table->decimal('late_hours', 8, 2)->default(0);
            $table->decimal('undertime_hours', 8, 2)->default(0);
            $table->decimal('approved_ot_hours', 8, 2)->default(0);
            $table->decimal('gross_pay', 12, 2)->default(0);
            $table->decimal('ot_pay', 12, 2)->default(0);
            $table->decimal('late_deduction', 12, 2)->default(0);
            $table->decimal('undertime_deduction', 12, 2)->default(0);
            $table->decimal('absence_deduction', 12, 2)->default(0);
            $table->decimal('sss_deduction', 12, 2)->default(0);
            $table->decimal('pagibig_deduction', 12, 2)->default(0);
            $table->decimal('philhealth_deduction', 12, 2)->default(0);
            $table->decimal('tax_deduction', 12, 2)->default(0);
            $table->decimal('cash_advance_deduction', 12, 2)->default(0);
            $table->decimal('loan_deduction', 12, 2)->default(0);
            $table->decimal('other_deductions', 12, 2)->default(0);
            $table->decimal('total_deductions', 12, 2)->default(0);
            $table->decimal('net_pay', 12, 2)->default(0);
            $table->timestamps();

            $table->foreign('payroll_run_id')->references('id')->on('payroll_runs')->onDelete('cascade');
            $table->foreign('employee_id')->references('id')->on('employees')->onDelete('cascade');
            $table->unique(['payroll_run_id', 'employee_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payroll_items');
    }
};
