<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AttendanceDailySummary extends Model
{
    protected $table = 'attendance_daily_summary';

    protected $fillable = [
        'import_batch_id',
        'employee_code',
        'summary_date',
        'time_in',
        'break_out',
        'break_in',
        'time_out',
        'grace_used',
        'late_in_minutes',
        'undertime_break_out_minutes',
        'late_break_in_minutes',
        'ot_minutes',
        'total_hours',
        'missed_logs',
        'status',
    ];

    protected $casts = [
        'summary_date' => 'date',
        'grace_used' => 'boolean',
        'total_hours' => 'decimal:2',
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'employee_code', 'employee_code');
    }

    public function importBatch(): BelongsTo
    {
        return $this->belongsTo(AttendanceImportBatch::class, 'import_batch_id');
    }
}
