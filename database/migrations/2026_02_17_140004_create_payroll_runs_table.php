<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payroll_runs', function (Blueprint $table) {
            $table->unsignedBigInteger('id')->primary();
            $table->string('uuid', 36)->unique();
            $table->string('name')->nullable();
            $table->date('period_start');
            $table->date('period_end');
            $table->string('mode', 20)->default('custom');
            $table->decimal('base_hours_per_day', 5, 2)->default(8);
            $table->decimal('ot_multiplier', 5, 2)->default(1);
            $table->unsignedBigInteger('batch_id')->nullable();
            $table->string('status', 20)->default('draft');
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('approved_by')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->foreign('batch_id')->references('id')->on('attendance_import_batches')->onDelete('set null');
            $table->foreign('created_by')->references('id')->on('employees')->onDelete('set null');
            $table->foreign('approved_by')->references('id')->on('employees')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payroll_runs');
    }
};
