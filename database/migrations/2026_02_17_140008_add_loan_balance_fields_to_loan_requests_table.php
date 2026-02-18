<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('loan_requests', function (Blueprint $table) {
            $table->decimal('monthly_amortization', 12, 2)->nullable()->after('term_months');
            $table->decimal('total_paid', 12, 2)->default(0)->after('monthly_amortization');
            $table->decimal('remaining_balance', 12, 2)->nullable()->after('total_paid');
            $table->string('loan_status', 20)->default('pending')->after('remaining_balance');
        });
    }

    public function down(): void
    {
        Schema::table('loan_requests', function (Blueprint $table) {
            $table->dropColumn(['monthly_amortization', 'total_paid', 'remaining_balance', 'loan_status']);
        });
    }
};
