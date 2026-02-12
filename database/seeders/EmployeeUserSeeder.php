<?php

namespace Database\Seeders;

use App\Models\Employee;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class EmployeeUserSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();

        $employees = Employee::query()
            ->where('employee_code', '!=', 'ADMIN-0001')
            ->get(['id', 'employee_code', 'first_name', 'last_name']);

        foreach ($employees as $employee) {
            $email = Str::lower($employee->employee_code) . '@local.test';
            $name = trim(($employee->first_name ?? '') . ' ' . ($employee->last_name ?? ''));
            if ($name === '') {
                $name = $employee->employee_code;
            }

            User::query()->updateOrCreate(
                ['email' => $email],
                [
                    'name' => $name,
                    'password' => 'password',
                    'employee_id' => $employee->id,
                    'email_verified_at' => $now,
                    'updated_at' => $now,
                    'created_at' => $now,
                ]
            );
        }
    }
}
