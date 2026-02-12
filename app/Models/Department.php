<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Department extends Model
{
    protected $fillable = [
        'id',
        'name',
        'description',
        'business_hours_start',
        'business_hours_end',
    ];

    public $incrementing = false;
    protected $keyType = 'int';

    public function employees(): HasMany
    {
        return $this->hasMany(Employee::class);
    }
}
