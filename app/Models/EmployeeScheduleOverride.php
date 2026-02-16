<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmployeeScheduleOverride extends Model
{
    protected $fillable = [
        'id',
        'employee_id',
        'work_date',
        'is_working',
        'start_time',
        'end_time',
    ];

    public $incrementing = false;
    protected $keyType = 'int';

    protected $casts = [
        'work_date' => 'date',
        'is_working' => 'boolean',
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }
}
