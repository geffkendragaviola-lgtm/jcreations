<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('loan_payments', function (Blueprint $table) {
            $table->unsignedBigInteger('id')->primary();
            $table->unsignedBigInteger('loan_request_id');
            $table->unsignedBigInteger('employee_id');
            $table->unsignedBigInteger('payroll_run_id')->nullable();
            $table->decimal('amount', 12, 2);
            $table->date('payment_date');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->foreign('loan_request_id')->references('id')->on('loan_requests')->onDelete('cascade');
            $table->foreign('employee_id')->references('id')->on('employees')->onDelete('cascade');
            $table->foreign('payroll_run_id')->references('id')->on('payroll_runs')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('loan_payments');
    }
};
