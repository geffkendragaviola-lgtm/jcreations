<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DisciplinaryAction extends Model
{
    protected $fillable = [
        'id',
        'employee_id',
        'type',
        'severity',
        'incident_date',
        'description',
        'action_taken',
        'status',
        'resolution_date',
        'resolution_notes',
        'issued_by',
        'attachment_path',
    ];

    public $incrementing = false;
    protected $keyType = 'int';

    protected $casts = [
        'incident_date' => 'date',
        'resolution_date' => 'date',
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function issuer(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'issued_by');
    }
}
