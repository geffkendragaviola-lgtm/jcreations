<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Holiday extends Model
{
    protected $fillable = [
        'id',
        'name',
        'date',
        'type',
        'pay_multiplier',
        'description',
        'recurring',
    ];

    public $incrementing = false;
    protected $keyType = 'int';

    protected $casts = [
        'date' => 'date',
        'pay_multiplier' => 'decimal:2',
        'recurring' => 'boolean',
    ];
}
