<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('loan_requests', function (Blueprint $table) {
            if (!Schema::hasColumn('loan_requests', 'approved_at')) {
                $table->timestamp('approved_at')->nullable()->after('approved_by');
                $table->index(['status', 'approved_at'], 'idx_loan_status_approved_at');
            }
        });
    }

    public function down(): void
    {
        Schema::table('loan_requests', function (Blueprint $table) {
            if (Schema::hasColumn('loan_requests', 'approved_at')) {
                $table->dropIndex('idx_loan_status_approved_at');
                $table->dropColumn('approved_at');
            }
        });
    }
};
