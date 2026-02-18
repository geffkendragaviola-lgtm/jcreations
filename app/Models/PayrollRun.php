<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PayrollRun extends Model
{
    protected $fillable = [
        'id',
        'uuid',
        'name',
        'period_start',
        'period_end',
        'mode',
        'base_hours_per_day',
        'ot_multiplier',
        'batch_id',
        'status',
        'created_by',
        'approved_by',
        'approved_at',
        'notes',
    ];

    public $incrementing = false;
    protected $keyType = 'int';

    protected $casts = [
        'period_start' => 'date',
        'period_end' => 'date',
        'base_hours_per_day' => 'decimal:2',
        'ot_multiplier' => 'decimal:2',
        'approved_at' => 'datetime',
    ];

    public function items(): HasMany
    {
        return $this->hasMany(PayrollItem::class);
    }

    public function batch(): BelongsTo
    {
        return $this->belongsTo(AttendanceImportBatch::class, 'batch_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'created_by');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'approved_by');
    }
}
