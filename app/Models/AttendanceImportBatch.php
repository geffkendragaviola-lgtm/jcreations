<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AttendanceImportBatch extends Model
{
    protected $table = 'attendance_import_batches';

    protected $fillable = [
        'uuid',
        'source_filename',
        'date_start',
        'date_end',
        'uploaded_by',
    ];

    protected $casts = [
        'date_start' => 'date',
        'date_end' => 'date',
    ];

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function logs(): HasMany
    {
        return $this->hasMany(AttendanceLog::class, 'import_batch_id');
    }

    public function dailySummaries(): HasMany
    {
        return $this->hasMany(AttendanceDailySummary::class, 'import_batch_id');
    }

    public function periodSummaries(): HasMany
    {
        return $this->hasMany(AttendancePeriodSummary::class, 'import_batch_id');
    }
}
