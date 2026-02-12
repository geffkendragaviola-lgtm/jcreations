<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AttendanceLog extends Model
{
    protected $table = 'attendance_logs';

    protected $fillable = [
        'import_batch_id',
        'employee_code',
        'department_snapshot',
        'employee_name_snapshot',
        'log_date',
        'log_time',
        'activity',
        'punch_type',
        'image',
        'address',
    ];

    protected $casts = [
        'log_date' => 'date',
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
