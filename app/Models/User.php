<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'employee_id',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    // Check if user has specific role
    public function hasRole($roleName)
    {
        return $this->employee && $this->employee->roles()->where('name', $roleName)->exists();
    }

    // Check if user is admin/manager
    public function isAdmin()
    {
        return $this->hasRole('admin') || $this->hasRole('manager');
    }

    public function canManageBackoffice(): bool
    {
        return $this->hasRole('admin') || $this->hasRole('hr') || $this->hasRole('sales clerk');
    }

    public function isManager()
    {
        return $this->hasRole('manager');
    }

    public function isEmployee()
    {
        return $this->hasRole('employee');
    }
}