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
        Schema::create('late_requests', function (Blueprint $table) {
            $table->integer('id')->primary();
            $table->foreignId('employee_id')->constrained('employees')->cascadeOnDelete();
            $table->date('date');
            $table->string('type', 30);
            $table->integer('minutes')->nullable();
            $table->text('reason')->nullable();
            $table->string('attachment_path')->nullable();
            $table->string('status', 20)->default('pending');
            $table->integer('approved_by')->nullable();
            $table->foreign('approved_by')->references('id')->on('employees')->nullOnDelete();
            $table->timestamps();

            $table->index(['employee_id', 'date']);
            $table->index(['status', 'date']);
        });

        DB::statement("ALTER TABLE late_requests ADD CONSTRAINT late_requests_status_check CHECK (status IN ('pending', 'approved', 'rejected'))");
        DB::statement("ALTER TABLE late_requests ADD CONSTRAINT late_requests_type_check CHECK (type IN ('late', 'undertime', 'missed_logs'))");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('late_requests');
    }
};
