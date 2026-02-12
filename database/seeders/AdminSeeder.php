<?php

namespace Database\Seeders;

use App\Models\Employee;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();

        $adminEmployee = Employee::query()->updateOrCreate(
            ['employee_code' => 'ADMIN-0001'],
            [
                'id' => 9999,
                'first_name' => 'Admin',
                'middle_name' => null,
                'last_name' => 'User',
                'email' => 'admin@local.test',
                'phone' => null,
                'department_id' => null,
                'position' => 'Administrator',
                'manager_id' => null,
                'updated_at' => $now,
                'created_at' => $now,
            ]
        );

        User::query()->updateOrCreate(
            ['email' => 'admin@local.test'],
            [
                'name' => 'Admin',
                'password' => 'password',
                'employee_id' => $adminEmployee->id,
                'email_verified_at' => $now,
                'updated_at' => $now,
                'created_at' => $now,
            ]
        );

        $adminRoleId = Role::query()->where('name', 'admin')->value('id');
        if ($adminRoleId) {
            DB::table('employee_role')->upsert(
                [[
                    'employee_id' => $adminEmployee->id,
                    'role_id' => $adminRoleId,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]],
                ['employee_id', 'role_id'],
                ['updated_at']
            );
        }
    }
}
