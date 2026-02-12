<?php

namespace Database\Seeders;

use App\Models\Department;
use App\Models\Employee;
use App\Models\Role;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class EmployeeSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();

        $deptIdByName = Department::query()->pluck('id', 'name')->all();

        $employees = [
            ['employee_code' => 'SHOP2025-22', 'department' => 'Shop', 'full_name' => 'Magdale, Jay-ar B.'],
            ['employee_code' => 'SHOP2025-18', 'department' => 'Shop', 'full_name' => 'Mariquit, "Consorcio jr."'],
            ['employee_code' => 'SHOP2025-08', 'department' => 'Shop', 'full_name' => 'Manlupig, Darius Sagrado'],
            ['employee_code' => 'ECO2025-05', 'department' => 'Ecotrade', 'full_name' => 'Fama, Ryan Luo Tubio'],
            ['employee_code' => 'ECO2025-21', 'department' => 'Ecotrade', 'full_name' => 'Ligutom, Judeirick'],
            ['employee_code' => 'JC2025-02', 'department' => 'Ecotrade', 'full_name' => 'CONCERMAN, Daryl Terec'],
            ['employee_code' => 'SHOP2025-20', 'department' => 'Shop', 'full_name' => 'Tekong, Ronnie C.'],
            ['employee_code' => 'SHOP2025-24', 'department' => 'Shop', 'full_name' => 'Bangcong, Ulysses'],
            ['employee_code' => 'JC2025-06', 'department' => 'Ecotrade', 'full_name' => 'Reysoma, Marijane Mariquit'],
            ['employee_code' => 'JCT2025-12', 'department' => 'JCT', 'full_name' => 'TACAISAN, RONALYN ANGKI'],
            ['employee_code' => 'JCT2025-19', 'department' => 'JCT', 'full_name' => 'Vequizo, Loui Givney Y.'],
            ['employee_code' => 'CT2025-13', 'department' => 'CT Print Stop', 'full_name' => 'Mariquit, Roselyn Jorgil'],
            ['employee_code' => 'JCT2025-14', 'department' => 'Shop / Eco', 'full_name' => 'Macalaguing, John lee Quinanahan'],
            ['employee_code' => 'JC2025-01', 'department' => 'Ecotrade', 'full_name' => 'Caballero, Julie Anne Dela Peña'],
            ['employee_code' => 'CT2025-10', 'department' => 'CT Print Stop', 'full_name' => 'Micabalo, Reggie Ann Moaña'],
            ['employee_code' => 'CT2025-11', 'department' => 'CT Print Stop', 'full_name' => 'Abong, Mylin Partulan'],
            ['employee_code' => 'ECO2025-04', 'department' => 'Ecotrade', 'full_name' => 'Miñoza, Regie Galgao'],
            ['employee_code' => 'JC2025-09', 'department' => 'JCT', 'full_name' => 'Abella, Medel Omandam'],
            ['employee_code' => 'SHOP2025-23', 'department' => 'Shop', 'full_name' => 'ABONG, MELVIN TUONG'],
            ['employee_code' => 'ECO2025-26', 'department' => 'Ecotrade', 'full_name' => 'BANO, ANTHONY MEGRENIO'],
            ['employee_code' => 'JCT2025-07', 'department' => 'JCT', 'full_name' => 'Patua, Lovely Romitares'],
            ['employee_code' => 'JC2025-16', 'department' => 'CT Print Stop', 'full_name' => 'LEURAG, ALORNA MANANGKI'],
        ];

        $rows = [];
        $nextId = 1001;

        foreach ($employees as $e) {
            [$lastName, $rest] = $this->splitLastName($e['full_name']);
            [$firstName, $middleName] = $this->splitFirstMiddle($rest);

            $deptName = $this->normalizeDepartmentName($e['department']);
            $departmentId = $deptIdByName[$deptName] ?? null;

            $rows[] = [
                'id' => $nextId++,
                'employee_code' => $e['employee_code'],
                'first_name' => $firstName !== '' ? $firstName : 'Unknown',
                'middle_name' => $middleName !== '' ? $middleName : null,
                'last_name' => $lastName !== '' ? $lastName : 'Unknown',
                'email' => null,
                'phone' => null,
                'department_id' => $departmentId,
                'position' => null,
                'manager_id' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        DB::transaction(function () use ($rows, $now) {
            Employee::query()->upsert(
                $rows,
                ['employee_code'],
                ['first_name', 'middle_name', 'last_name', 'department_id', 'position', 'manager_id', 'updated_at']
            );

            $employeeRoleId = Role::query()->where('name', 'employee')->value('id');
            if ($employeeRoleId) {
                $employeeIds = Employee::query()->whereIn('employee_code', array_column($rows, 'employee_code'))->pluck('id')->all();

                $pivotRows = array_map(function ($employeeId) use ($employeeRoleId, $now) {
                    return [
                        'employee_id' => $employeeId,
                        'role_id' => $employeeRoleId,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ];
                }, $employeeIds);

                DB::table('employee_role')->upsert(
                    $pivotRows,
                    ['employee_id', 'role_id'],
                    ['updated_at']
                );
            }
        });
    }

    private function normalizeDepartmentName(string $dept): string
    {
        $key = preg_replace('/\s+/', ' ', Str::lower(trim($dept)));
        if ($key === 'ct print stop' || $key === 'ct print shop') {
            return 'CT Print Stop';
        }
        if ($key === 'eco trade' || $key === 'ecotrade') {
            return 'Ecotrade';
        }
        if ($key === 'shop / eco' || $key === 'shop/eco') {
            return 'Shop / Eco';
        }
        if ($key === 'shop') {
            return 'Shop';
        }
        if ($key === 'jct') {
            return 'JCT';
        }

        return trim($dept);
    }

    private function splitLastName(string $fullName): array
    {
        $s = trim($fullName);
        $pos = strpos($s, ',');
        if ($pos === false) {
            return ['', $s];
        }
        $last = trim(substr($s, 0, $pos));
        $rest = trim(substr($s, $pos + 1));
        return [$last, $rest];
    }

    private function splitFirstMiddle(string $rest): array
    {
        $s = trim($rest);
        if ($s === '') {
            return ['', ''];
        }
        $parts = preg_split('/\s+/', $s);
        if (!$parts || count($parts) === 0) {
            return [$s, ''];
        }

        $first = trim(array_shift($parts));
        $middle = trim(implode(' ', $parts));

        return [$first, $middle];
    }
}
