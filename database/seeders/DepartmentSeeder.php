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
            ['id' => 1, 'name' => 'Shop', 'business_hours_start' => '08:00:00', 'business_hours_end' => '17:00:00'],
            ['id' => 2, 'name' => 'Ecotrade', 'business_hours_start' => '08:30:00', 'business_hours_end' => '17:30:00'],
            ['id' => 3, 'name' => 'JCT', 'business_hours_start' => '09:00:00', 'business_hours_end' => '18:00:00'],
            ['id' => 4, 'name' => 'CT Print Stop', 'business_hours_start' => '08:30:00', 'business_hours_end' => '17:30:00'],
            ['id' => 5, 'name' => 'Shop / Eco', 'business_hours_start' => '08:30:00', 'business_hours_end' => '17:30:00'],
           
        ];

        $rows = array_map(function ($d) use ($now) {
            return [
                'id' => $d['id'],
                'name' => $d['name'],
                'description' => null,
                'business_hours_start' => $d['business_hours_start'] ?? null,
                'business_hours_end' => $d['business_hours_end'] ?? null,
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
