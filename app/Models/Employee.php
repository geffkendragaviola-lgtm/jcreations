<?php

namespace App\Models;

use App\Models\AttendanceDailySummary;
use App\Models\AttendanceLog;
use App\Models\Department;
use App\Models\EmployeeSchedule;
use App\Models\EmployeeScheduleOverride;
use App\Models\LeaveRequest;
use App\Models\OvertimeRequest;
use App\Models\CashAdvanceRequest;
use App\Models\LoanRequest;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Employee extends Model
{
    protected $fillable = [
        'id',
        'employee_code',
        'first_name',
        'middle_name',
        'last_name',
        'email',
        'phone',
        'work_email',
        'work_phone',
        'work_mobile',
        'bank_account_no',
        'sss_no',
        'philhealth_no',
        'hdmf_no',
        'tax_id_no',
        'department_id',
        'position',
        'daily_rate',
        'wage',
        'hourly_rate',
        'hourly_rate_overtime',
        'government_deduction',
        'sss_deduction',
        'pagibig_deduction',
        'philhealth_deduction',
        'cash_advance_deduction',
        'manager_id',
        'contract_start_date',
        'contract_end_date',
        'working_schedule',
        'minimum_wage_earner',
        'salary_structure_type',
        'contract_type',
        'salary_schedule_pay',
        'salary_structure',
    ];

    public $incrementing = false;
    protected $keyType = 'int';

    protected $casts = [
        'daily_rate' => 'decimal:2',
        'wage' => 'decimal:2',
        'hourly_rate' => 'decimal:4',
        'hourly_rate_overtime' => 'decimal:4',
        'government_deduction' => 'decimal:2',
        'sss_deduction' => 'decimal:2',
        'pagibig_deduction' => 'decimal:2',
        'philhealth_deduction' => 'decimal:2',
        'cash_advance_deduction' => 'decimal:2',
        'contract_start_date' => 'date',
        'contract_end_date' => 'date',
        'minimum_wage_earner' => 'boolean',
    ];

    public function user(): HasOne
    {
        return $this->hasOne(User::class);
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function manager(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'manager_id');
    }

    public function subordinates(): HasMany
    {
        return $this->hasMany(Employee::class, 'manager_id');
    }

    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'employee_role');
    }

    public function leaveRequests(): HasMany
    {
        return $this->hasMany(LeaveRequest::class);
    }

    public function overtimeRequests(): HasMany
    {
        return $this->hasMany(OvertimeRequest::class);
    }

    public function cashAdvanceRequests(): HasMany
    {
        return $this->hasMany(CashAdvanceRequest::class);
    }

    public function loanRequests(): HasMany
    {
        return $this->hasMany(LoanRequest::class);
    }

    public function schedules(): HasMany
    {
        return $this->hasMany(EmployeeSchedule::class);
    }

    public function scheduleOverrides(): HasMany
    {
        return $this->hasMany(EmployeeScheduleOverride::class);
    }

    public function attendanceLogs(): HasMany
    {
        return $this->hasMany(AttendanceLog::class, 'employee_code', 'employee_code');
    }

    public function dailySummaries(): HasMany
    {
        return $this->hasMany(AttendanceDailySummary::class, 'employee_code', 'employee_code');
    }

    public function getFullNameAttribute(): string
    {
        return $this->first_name . ' ' . ($this->middle_name ? $this->middle_name . ' ' : '') . $this->last_name;
    }
}