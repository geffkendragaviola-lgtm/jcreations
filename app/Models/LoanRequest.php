<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LoanRequest extends Model
{
    protected $fillable = [
        'id',
        'employee_id',
        'amount',
        'term_months',
        'purpose',
        'status',
        'approved_by',
        'admin_notes',
        'attachment_path',
    ];

    public $incrementing = false;
    protected $keyType = 'int';

    protected $casts = [
        'amount' => 'decimal:2',
        'term_months' => 'int',
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'approved_by');
    }
}
