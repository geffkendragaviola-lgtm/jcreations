<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();

        $roles = [
            ['id' => 1, 'name' => 'admin', 'description' => 'System administrator'],
            ['id' => 2, 'name' => 'manager', 'description' => 'Department manager'],
            ['id' => 3, 'name' => 'employee', 'description' => 'Regular employee'],
            ['id' => 4, 'name' => 'hr', 'description' => 'Human resources'],
            ['id' => 5, 'name' => 'sales clerk', 'description' => 'Sales clerk'],
        ];

        $rows = array_map(function ($r) use ($now) {
            return [
                'id' => $r['id'],
                'name' => $r['name'],
                'description' => $r['description'],
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }, $roles);

        Role::query()->upsert(
            $rows,
            ['id'],
            ['name', 'description', 'updated_at']
        );
    }
}
