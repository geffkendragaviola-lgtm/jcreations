<?php

namespace Database\Seeders;

use App\Models\Department;
use Illuminate\Database\Seeder;

class DepartmentSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();

        $departments = [
            ['id' => 1, 'name' => 'Shop'],
            ['id' => 2, 'name' => 'Ecotrade'],
            ['id' => 3, 'name' => 'JCT'],
            ['id' => 4, 'name' => 'CT Print Stop'],
            ['id' => 5, 'name' => 'Shop / Eco'],
            ['id' => 6, 'name' => 'JCT Print Stop'],
        ];

        $rows = array_map(function ($d) use ($now) {
            return [
                'id' => $d['id'],
                'name' => $d['name'],
                'description' => null,
                'business_hours_start' => null,
                'business_hours_end' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }, $departments);

        Department::query()->upsert(
            $rows,
            ['id'],
            ['name', 'description', 'business_hours_start', 'business_hours_end', 'updated_at']
        );
    }
}
