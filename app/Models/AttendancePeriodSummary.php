<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AttendancePeriodSummary extends Model
{
    protected $table = 'attendance_period_summary';

    protected $fillable = [
        'import_batch_id',
        'employee_code',
        'period_start',
        'period_end',
        'late_frequency',
        'missed_logs_count',
        'grace_days',
        'absences',
        'days_worked',
        'late_duration',
        'avg_late_per_occurrence',
        'total_undertime',
        'undertime_frequency',
        'most_frequent_late_time',
        'letter_generated',
        'letter_reference',
    ];

    protected $casts = [
        'period_start' => 'date',
        'period_end' => 'date',
        'letter_generated' => 'boolean',
        'avg_late_per_occurrence' => 'decimal:2',
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
