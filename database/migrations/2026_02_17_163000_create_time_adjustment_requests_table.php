<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('time_adjustment_requests', function (Blueprint $table) {
            $table->integer('id')->primary();
            $table->foreignId('employee_id')->constrained('employees')->cascadeOnDelete();
            $table->date('date');
            $table->string('adjustment_type', 40);
            $table->integer('minutes')->nullable();
            $table->text('reason')->nullable();
            $table->string('attachment_path')->nullable();
            $table->string('status', 20)->default('pending');
            $table->integer('approved_by')->nullable();
            $table->foreign('approved_by')->references('id')->on('employees')->nullOnDelete();
            $table->text('admin_notes')->nullable();
            $table->timestamps();

            $table->index(['employee_id', 'date']);
            $table->index(['status', 'date']);
        });

        DB::statement("ALTER TABLE time_adjustment_requests ADD CONSTRAINT time_adjustment_requests_status_check CHECK (status IN ('pending', 'approved', 'rejected'))");
        DB::statement("ALTER TABLE time_adjustment_requests ADD CONSTRAINT time_adjustment_requests_type_check CHECK (adjustment_type IN ('planned_late', 'planned_early_out', 'half_day', 'official_business', 'emergency_short_hours'))");
    }

    public function down(): void
    {
        Schema::dropIfExists('time_adjustment_requests');
    }
};
