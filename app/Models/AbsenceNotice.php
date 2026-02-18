<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AbsenceNotice extends Model
{
    protected $fillable = [
        'id',
        'employee_id',
        'import_batch_id',
        'date',
        'detected_from_summary',
        'reason',
        'attachment_path',
        'status',
        'approved_by',
        'admin_notes',
    ];

    public $incrementing = false;
    protected $keyType = 'int';

    protected $casts = [
        'date' => 'date',
        'detected_from_summary' => 'bool',
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
