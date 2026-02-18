<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('cash_advance_requests', function (Blueprint $table) {
            if (!Schema::hasColumn('cash_advance_requests', 'approved_at')) {
                $table->timestamp('approved_at')->nullable()->after('approved_by');
            }

            if (!Schema::hasColumn('cash_advance_requests', 'deducted_payroll_run_id')) {
                $table->unsignedBigInteger('deducted_payroll_run_id')->nullable()->after('approved_at');
                $table->foreign('deducted_payroll_run_id')->references('id')->on('payroll_runs')->nullOnDelete();
                $table->index(['deducted_payroll_run_id', 'employee_id'], 'idx_cash_adv_deducted_run_employee');
            }

            if (!Schema::hasColumn('cash_advance_requests', 'deducted_at')) {
                $table->timestamp('deducted_at')->nullable()->after('deducted_payroll_run_id');
            }

            if (!Schema::hasColumn('cash_advance_requests', 'deduction_amount')) {
                $table->decimal('deduction_amount', 12, 2)->nullable()->after('amount');
            }
        });

        if (DB::getDriverName() === 'pgsql') {
            DB::statement('CREATE INDEX IF NOT EXISTS idx_cash_adv_status_approved_at ON cash_advance_requests (status, approved_at)');
        } else {
            Schema::table('cash_advance_requests', function (Blueprint $table) {
                $table->index(['status', 'approved_at'], 'idx_cash_adv_status_approved_at');
            });
        }
    }

    public function down(): void
    {
        Schema::table('cash_advance_requests', function (Blueprint $table) {
            if (Schema::hasColumn('cash_advance_requests', 'deducted_payroll_run_id')) {
                $table->dropForeign(['deducted_payroll_run_id']);
                $table->dropIndex('idx_cash_adv_deducted_run_employee');
                $table->dropColumn('deducted_payroll_run_id');
            }

            if (Schema::hasColumn('cash_advance_requests', 'deducted_at')) {
                $table->dropColumn('deducted_at');
            }

            if (Schema::hasColumn('cash_advance_requests', 'approved_at')) {
                $table->dropColumn('approved_at');
            }

            if (Schema::hasColumn('cash_advance_requests', 'deduction_amount')) {
                $table->dropColumn('deduction_amount');
            }
        });

        if (DB::getDriverName() === 'pgsql') {
            DB::statement('DROP INDEX IF EXISTS idx_cash_adv_status_approved_at');
        } else {
            Schema::table('cash_advance_requests', function (Blueprint $table) {
                $table->dropIndex('idx_cash_adv_status_approved_at');
            });
        }
    }
};
