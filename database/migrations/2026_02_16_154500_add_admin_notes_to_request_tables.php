<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('leave_requests', function (Blueprint $table) {
            if (!Schema::hasColumn('leave_requests', 'admin_notes')) {
                $table->text('admin_notes')->nullable()->after('approved_by');
            }
        });

        Schema::table('overtime_requests', function (Blueprint $table) {
            if (!Schema::hasColumn('overtime_requests', 'admin_notes')) {
                $table->text('admin_notes')->nullable()->after('approved_by');
            }
        });

        Schema::table('late_requests', function (Blueprint $table) {
            if (!Schema::hasColumn('late_requests', 'admin_notes')) {
                $table->text('admin_notes')->nullable()->after('approved_by');
            }
        });
    }

    public function down(): void
    {
        Schema::table('leave_requests', function (Blueprint $table) {
            if (Schema::hasColumn('leave_requests', 'admin_notes')) {
                $table->dropColumn('admin_notes');
            }
        });

        Schema::table('overtime_requests', function (Blueprint $table) {
            if (Schema::hasColumn('overtime_requests', 'admin_notes')) {
                $table->dropColumn('admin_notes');
            }
        });

        Schema::table('late_requests', function (Blueprint $table) {
            if (Schema::hasColumn('late_requests', 'admin_notes')) {
                $table->dropColumn('admin_notes');
            }
        });
    }
};
