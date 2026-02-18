<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LeaveBalance extends Model
{
    protected $fillable = [
        'id',
        'employee_id',
        'leave_type',
        'year',
        'total_credits',
        'used',
        'remaining',
    ];

    public $incrementing = false;
    protected $keyType = 'int';

    protected $casts = [
        'year' => 'int',
        'total_credits' => 'decimal:2',
        'used' => 'decimal:2',
        'remaining' => 'decimal:2',
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }
}
