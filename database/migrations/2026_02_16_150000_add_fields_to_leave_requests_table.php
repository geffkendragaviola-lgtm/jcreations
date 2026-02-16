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
        Schema::table('leave_requests', function (Blueprint $table) {
            if (!Schema::hasColumn('leave_requests', 'day_type')) {
                $table->string('day_type', 20)->default('full_day')->after('end_date');
            }
            if (!Schema::hasColumn('leave_requests', 'duration_days')) {
                $table->decimal('duration_days', 6, 2)->default(0)->after('day_type');
            }
            if (!Schema::hasColumn('leave_requests', 'description')) {
                $table->text('description')->nullable()->after('duration_days');
            }
            if (!Schema::hasColumn('leave_requests', 'attachment_path')) {
                $table->string('attachment_path')->nullable()->after('description');
            }
        });

        try {
            DB::statement("ALTER TABLE leave_requests ADD CONSTRAINT leave_requests_day_type_check CHECK (day_type IN ('full_day', 'half_day'))");
        } catch (\Throwable $e) {
            // ignore (e.g. constraint already exists / driver doesn't support)
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('leave_requests', function (Blueprint $table) {
            if (Schema::hasColumn('leave_requests', 'attachment_path')) {
                $table->dropColumn('attachment_path');
            }
            if (Schema::hasColumn('leave_requests', 'description')) {
                $table->dropColumn('description');
            }
            if (Schema::hasColumn('leave_requests', 'duration_days')) {
                $table->dropColumn('duration_days');
            }
            if (Schema::hasColumn('leave_requests', 'day_type')) {
                $table->dropColumn('day_type');
            }
        });

        try {
            DB::statement('ALTER TABLE leave_requests DROP CONSTRAINT leave_requests_day_type_check');
        } catch (\Throwable $e) {
            // ignore
        }
    }
};
