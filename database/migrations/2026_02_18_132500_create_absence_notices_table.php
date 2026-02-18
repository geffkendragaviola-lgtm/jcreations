<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('absence_notices', function (Blueprint $table) {
            $table->integer('id')->primary();
            $table->foreignId('employee_id')->constrained('employees')->cascadeOnDelete();
            $table->unsignedBigInteger('import_batch_id')->nullable();
            $table->foreign('import_batch_id')->references('id')->on('attendance_import_batches')->nullOnDelete();
            $table->date('date');
            $table->boolean('detected_from_summary')->default(false);
            $table->text('reason')->nullable();
            $table->string('attachment_path')->nullable();
            $table->string('status', 20)->default('pending');
            $table->unsignedBigInteger('approved_by')->nullable();
            $table->foreign('approved_by')->references('id')->on('employees')->nullOnDelete();
            $table->text('admin_notes')->nullable();
            $table->timestamps();

            $table->index(['employee_id', 'date']);
            $table->index(['status', 'date']);
            $table->index(['import_batch_id', 'date'], 'idx_absence_notices_batch_date');
            $table->index(['detected_from_summary', 'status'], 'idx_absence_notices_detected_status');
        });

        DB::statement("ALTER TABLE absence_notices ADD CONSTRAINT absence_notices_status_check CHECK (status IN ('pending', 'approved', 'rejected'))");
    }

    public function down(): void
    {
        Schema::dropIfExists('absence_notices');
    }
};
