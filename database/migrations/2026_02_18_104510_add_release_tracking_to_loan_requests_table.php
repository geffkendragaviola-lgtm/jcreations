<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('loan_requests', function (Blueprint $table) {
            if (!Schema::hasColumn('loan_requests', 'released_at')) {
                $table->timestamp('released_at')->nullable()->after('approved_at');
            }

            if (!Schema::hasColumn('loan_requests', 'released_by')) {
                $table->unsignedBigInteger('released_by')->nullable()->after('released_at');
                $table->foreign('released_by')->references('id')->on('employees')->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('loan_requests', function (Blueprint $table) {
            if (Schema::hasColumn('loan_requests', 'released_by')) {
                $table->dropForeign(['released_by']);
                $table->dropColumn('released_by');
            }

            if (Schema::hasColumn('loan_requests', 'released_at')) {
                $table->dropColumn('released_at');
            }
        });
    }
};
