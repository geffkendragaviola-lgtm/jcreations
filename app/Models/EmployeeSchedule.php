<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmployeeSchedule extends Model
{
    protected $table = 'employee_schedules';

    protected $fillable = [
        'id',
        'employee_id',
        'day_of_week',
        'start_time',
        'end_time',
    ];

    public $incrementing = false;
    protected $keyType = 'int';

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }
}
