<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            if (!Schema::hasColumn('employees', 'work_email')) {
                $table->string('work_email', 100)->nullable()->after('phone');
            }
            if (!Schema::hasColumn('employees', 'work_phone')) {
                $table->string('work_phone', 20)->nullable()->after('work_email');
            }
            if (!Schema::hasColumn('employees', 'work_mobile')) {
                $table->string('work_mobile', 20)->nullable()->after('work_phone');
            }
            if (!Schema::hasColumn('employees', 'bank_account_no')) {
                $table->string('bank_account_no', 50)->nullable()->after('work_mobile');
            }

            if (!Schema::hasColumn('employees', 'sss_no')) {
                $table->string('sss_no', 50)->nullable()->after('bank_account_no');
            }
            if (!Schema::hasColumn('employees', 'philhealth_no')) {
                $table->string('philhealth_no', 50)->nullable()->after('sss_no');
            }
            if (!Schema::hasColumn('employees', 'hdmf_no')) {
                $table->string('hdmf_no', 50)->nullable()->after('philhealth_no');
            }
            if (!Schema::hasColumn('employees', 'tax_id_no')) {
                $table->string('tax_id_no', 50)->nullable()->after('hdmf_no');
            }

            if (!Schema::hasColumn('employees', 'contract_start_date')) {
                $table->date('contract_start_date')->nullable()->after('manager_id');
            }
            if (!Schema::hasColumn('employees', 'contract_end_date')) {
                $table->date('contract_end_date')->nullable()->after('contract_start_date');
            }
            if (!Schema::hasColumn('employees', 'working_schedule')) {
                $table->string('working_schedule', 100)->nullable()->after('contract_end_date');
            }
            if (!Schema::hasColumn('employees', 'minimum_wage_earner')) {
                $table->boolean('minimum_wage_earner')->default(false)->after('working_schedule');
            }
            if (!Schema::hasColumn('employees', 'salary_structure_type')) {
                $table->string('salary_structure_type', 50)->nullable()->after('minimum_wage_earner');
            }
            if (!Schema::hasColumn('employees', 'contract_type')) {
                $table->string('contract_type', 50)->nullable()->after('salary_structure_type');
            }
            if (!Schema::hasColumn('employees', 'salary_schedule_pay')) {
                $table->string('salary_schedule_pay', 50)->nullable()->after('contract_type');
            }
            if (!Schema::hasColumn('employees', 'salary_structure')) {
                $table->text('salary_structure')->nullable()->after('salary_schedule_pay');
            }

            if (!Schema::hasColumn('employees', 'wage')) {
                $table->decimal('wage', 10, 2)->default(0)->after('daily_rate');
            }
            if (!Schema::hasColumn('employees', 'hourly_rate')) {
                $table->decimal('hourly_rate', 10, 4)->default(0)->after('wage');
            }
            if (!Schema::hasColumn('employees', 'hourly_rate_overtime')) {
                $table->decimal('hourly_rate_overtime', 10, 4)->default(0)->after('hourly_rate');
            }
        });
    }

    public function down(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            $columns = [
                'work_email',
                'work_phone',
                'work_mobile',
                'bank_account_no',
                'sss_no',
                'philhealth_no',
                'hdmf_no',
                'tax_id_no',
                'contract_start_date',
                'contract_end_date',
                'working_schedule',
                'minimum_wage_earner',
                'salary_structure_type',
                'contract_type',
                'salary_schedule_pay',
                'salary_structure',
                'wage',
                'hourly_rate',
                'hourly_rate_overtime',
            ];

            $existing = [];
            foreach ($columns as $c) {
                if (Schema::hasColumn('employees', $c)) {
                    $existing[] = $c;
                }
            }

            if (count($existing) > 0) {
                $table->dropColumn($existing);
            }
        });
    }
};
