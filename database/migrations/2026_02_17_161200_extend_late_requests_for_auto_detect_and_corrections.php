<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('late_requests', function (Blueprint $table) {
            if (!Schema::hasColumn('late_requests', 'import_batch_id')) {
                $table->unsignedBigInteger('import_batch_id')->nullable()->after('employee_id');
                $table->foreign('import_batch_id')->references('id')->on('attendance_import_batches')->nullOnDelete();
                $table->index(['import_batch_id', 'date'], 'idx_late_requests_batch_date');
            }

            if (!Schema::hasColumn('late_requests', 'detected_from_summary')) {
                $table->boolean('detected_from_summary')->default(false)->after('minutes');
                $table->index(['detected_from_summary', 'status'], 'idx_late_requests_detected_status');
            }

            if (!Schema::hasColumn('late_requests', 'corrected_time_in')) {
                $table->time('corrected_time_in')->nullable()->after('attachment_path');
            }
            if (!Schema::hasColumn('late_requests', 'corrected_break_out')) {
                $table->time('corrected_break_out')->nullable()->after('corrected_time_in');
            }
            if (!Schema::hasColumn('late_requests', 'corrected_break_in')) {
                $table->time('corrected_break_in')->nullable()->after('corrected_break_out');
            }
            if (!Schema::hasColumn('late_requests', 'corrected_time_out')) {
                $table->time('corrected_time_out')->nullable()->after('corrected_break_in');
            }

            if (!Schema::hasColumn('late_requests', 'corrected_by')) {
                $table->unsignedBigInteger('corrected_by')->nullable()->after('approved_by');
                $table->foreign('corrected_by')->references('id')->on('employees')->nullOnDelete();
            }
            if (!Schema::hasColumn('late_requests', 'corrected_at')) {
                $table->timestamp('corrected_at')->nullable()->after('corrected_by');
            }
        });

        DB::statement("ALTER TABLE late_requests DROP CONSTRAINT IF EXISTS late_requests_type_check");
        DB::statement("ALTER TABLE late_requests ADD CONSTRAINT late_requests_type_check CHECK (type IN ('late', 'undertime', 'missed_logs', 'no_time_in', 'no_time_out'))");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE late_requests DROP CONSTRAINT IF EXISTS late_requests_type_check");
        DB::statement("ALTER TABLE late_requests ADD CONSTRAINT late_requests_type_check CHECK (type IN ('late', 'undertime', 'missed_logs'))");

        Schema::table('late_requests', function (Blueprint $table) {
            if (Schema::hasColumn('late_requests', 'corrected_at')) {
                $table->dropColumn('corrected_at');
            }

            if (Schema::hasColumn('late_requests', 'corrected_by')) {
                $table->dropForeign(['corrected_by']);
                $table->dropColumn('corrected_by');
            }

            if (Schema::hasColumn('late_requests', 'corrected_time_out')) {
                $table->dropColumn('corrected_time_out');
            }
            if (Schema::hasColumn('late_requests', 'corrected_break_in')) {
                $table->dropColumn('corrected_break_in');
            }
            if (Schema::hasColumn('late_requests', 'corrected_break_out')) {
                $table->dropColumn('corrected_break_out');
            }
            if (Schema::hasColumn('late_requests', 'corrected_time_in')) {
                $table->dropColumn('corrected_time_in');
            }

            if (Schema::hasColumn('late_requests', 'detected_from_summary')) {
                $table->dropIndex('idx_late_requests_detected_status');
                $table->dropColumn('detected_from_summary');
            }

            if (Schema::hasColumn('late_requests', 'import_batch_id')) {
                $table->dropIndex('idx_late_requests_batch_date');
                $table->dropForeign(['import_batch_id']);
                $table->dropColumn('import_batch_id');
            }
        });
    }
};
