<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('disciplinary_actions', function (Blueprint $table) {
            $table->unsignedBigInteger('id')->primary();
            $table->unsignedBigInteger('employee_id');
            $table->string('type', 50);
            $table->string('severity', 20)->default('minor');
            $table->date('incident_date');
            $table->text('description');
            $table->text('action_taken')->nullable();
            $table->string('status', 20)->default('open');
            $table->date('resolution_date')->nullable();
            $table->text('resolution_notes')->nullable();
            $table->unsignedBigInteger('issued_by')->nullable();
            $table->string('attachment_path')->nullable();
            $table->timestamps();

            $table->foreign('employee_id')->references('id')->on('employees')->onDelete('cascade');
            $table->foreign('issued_by')->references('id')->on('employees')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('disciplinary_actions');
    }
};
