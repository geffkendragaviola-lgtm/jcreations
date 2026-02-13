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
        Schema::table('employees', function (Blueprint $table) {
            $table->decimal('sss_deduction', 10, 2)->default(0)->after('government_deduction');
            $table->decimal('pagibig_deduction', 10, 2)->default(0)->after('sss_deduction');
            $table->decimal('philhealth_deduction', 10, 2)->default(0)->after('pagibig_deduction');
            $table->decimal('cash_advance_deduction', 10, 2)->default(0)->after('philhealth_deduction');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->dropColumn([
                'sss_deduction',
                'pagibig_deduction',
                'philhealth_deduction',
                'cash_advance_deduction',
            ]);
        });
    }
};
