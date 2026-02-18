<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PayrollItem extends Model
{
    protected $fillable = [
        'id',
        'payroll_run_id',
        'employee_id',
        'employee_code',
        'daily_rate',
        'hourly_rate',
        'regular_worked_days',
        'rest_day_worked_days',
        'paid_leave_days',
        'unpaid_leave_days',
        'unpaid_absence_days',
        'late_hours',
        'undertime_hours',
        'approved_ot_hours',
        'gross_pay',
        'ot_pay',
        'late_deduction',
        'undertime_deduction',
        'absence_deduction',
        'sss_deduction',
        'pagibig_deduction',
        'philhealth_deduction',
        'tax_deduction',
        'cash_advance_deduction',
        'loan_deduction',
        'other_deductions',
        'total_deductions',
        'net_pay',
    ];

    public $incrementing = false;
    protected $keyType = 'int';

    protected $casts = [
        'daily_rate' => 'decimal:2',
        'hourly_rate' => 'decimal:4',
        'regular_worked_days' => 'int',
        'rest_day_worked_days' => 'int',
        'paid_leave_days' => 'decimal:2',
        'unpaid_leave_days' => 'decimal:2',
        'unpaid_absence_days' => 'int',
        'late_hours' => 'decimal:2',
        'undertime_hours' => 'decimal:2',
        'approved_ot_hours' => 'decimal:2',
        'gross_pay' => 'decimal:2',
        'ot_pay' => 'decimal:2',
        'late_deduction' => 'decimal:2',
        'undertime_deduction' => 'decimal:2',
        'absence_deduction' => 'decimal:2',
        'sss_deduction' => 'decimal:2',
        'pagibig_deduction' => 'decimal:2',
        'philhealth_deduction' => 'decimal:2',
        'tax_deduction' => 'decimal:2',
        'cash_advance_deduction' => 'decimal:2',
        'loan_deduction' => 'decimal:2',
        'other_deductions' => 'decimal:2',
        'total_deductions' => 'decimal:2',
        'net_pay' => 'decimal:2',
    ];

    public function payrollRun(): BelongsTo
    {
        return $this->belongsTo(PayrollRun::class);
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }
}
