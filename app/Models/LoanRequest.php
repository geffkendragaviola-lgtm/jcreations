<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LoanRequest extends Model
{
    protected $fillable = [
        'id',
        'employee_id',
        'amount',
        'term_months',
        'monthly_amortization',
        'total_paid',
        'remaining_balance',
        'loan_status',
        'purpose',
        'status',
        'approved_by',
        'approved_at',
        'released_by',
        'released_at',
        'admin_notes',
        'attachment_path',
    ];

    public $incrementing = false;
    protected $keyType = 'int';

    protected $casts = [
        'amount' => 'decimal:2',
        'term_months' => 'int',
        'monthly_amortization' => 'decimal:2',
        'total_paid' => 'decimal:2',
        'remaining_balance' => 'decimal:2',
        'approved_at' => 'datetime',
        'released_at' => 'datetime',
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'approved_by');
    }

    public function releaser(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'released_by');
    }

    public function payments(): HasMany
    {
        return $this->hasMany(LoanPayment::class);
    }
}
