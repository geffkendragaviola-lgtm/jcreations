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
        Schema::table('attendance_logs', function (Blueprint $table) {
            $table->unsignedBigInteger('import_batch_id')->nullable()->after('id');
            $table->index(['import_batch_id', 'log_date'], 'idx_attendance_logs_batch_date');

            $table->foreign('import_batch_id')
                ->references('id')
                ->on('attendance_import_batches')
                ->nullOnDelete();
        });

        Schema::table('attendance_daily_summary', function (Blueprint $table) {
            $table->unsignedBigInteger('import_batch_id')->nullable()->after('id');
            $table->index(['import_batch_id', 'summary_date'], 'idx_attendance_daily_batch_date');

            $table->foreign('import_batch_id')
                ->references('id')
                ->on('attendance_import_batches')
                ->nullOnDelete();
        });

        Schema::table('attendance_period_summary', function (Blueprint $table) {
            $table->unsignedBigInteger('import_batch_id')->nullable()->after('id');
            $table->index(['import_batch_id', 'period_start', 'period_end'], 'idx_attendance_period_batch_range');

            $table->foreign('import_batch_id')
                ->references('id')
                ->on('attendance_import_batches')
                ->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('attendance_logs', function (Blueprint $table) {
            $table->dropForeign(['import_batch_id']);
            $table->dropIndex('idx_attendance_logs_batch_date');
            $table->dropColumn('import_batch_id');
        });

        Schema::table('attendance_daily_summary', function (Blueprint $table) {
            $table->dropForeign(['import_batch_id']);
            $table->dropIndex('idx_attendance_daily_batch_date');
            $table->dropColumn('import_batch_id');
        });

        Schema::table('attendance_period_summary', function (Blueprint $table) {
            $table->dropForeign(['import_batch_id']);
            $table->dropIndex('idx_attendance_period_batch_range');
            $table->dropColumn('import_batch_id');
        });
    }
};
