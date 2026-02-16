<?php

namespace App\Http\Controllers;

use App\Models\Department;
use App\Models\Employee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class EmployeeManagementController extends Controller
{
    private function redirectToEmployeesIndex(Request $request)
    {
        $redirectTo = $request->input('redirect_to');

        if (is_string($redirectTo) && $redirectTo !== '') {
            if (str_starts_with($redirectTo, '/')) {
                return redirect($redirectTo);
            }

            $appUrl = rtrim((string) config('app.url'), '/');
            if ($appUrl !== '' && str_starts_with($redirectTo, $appUrl)) {
                return redirect()->to($redirectTo);
            }
        }

        return redirect()->route('employees.index');
    }

    public function index(Request $request)
    {
        $user = $request->user();
        if (!$user?->canManageBackoffice()) {
            abort(403);
        }

        $search = trim((string) $request->query('search', ''));
        $departmentId = $request->query('department_id');

        $employees = Employee::query()
            ->with(['department', 'manager'])
            ->when($departmentId !== null && $departmentId !== '', function ($query) use ($departmentId) {
                $query->where('department_id', $departmentId);
            })
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('employee_code', 'like', '%' . $search . '%')
                        ->orWhere('first_name', 'like', '%' . $search . '%')
                        ->orWhere('last_name', 'like', '%' . $search . '%');
                });
            })
            ->orderBy('employee_code')
            ->paginate(25)
            ->withQueryString();

        $departments = Department::query()
            ->orderBy('name')
            ->get();

        $managers = Employee::query()
            ->orderBy('employee_code')
            ->get();

        return view('employees.index', [
            'employees' => $employees,
            'departments' => $departments,
            'managers' => $managers,
        ]);
    }

    public function show(Request $request, Employee $employee)
    {
        $user = $request->user();
        if (!$user?->canManageBackoffice()) {
            abort(403);
        }

        $employee->load(['department', 'manager']);

        return view('employees.show', [
            'employee' => $employee,
        ]);
    }

    public function update(Request $request, Employee $employee)
    {
        $user = $request->user();
        if (!$user?->canManageBackoffice()) {
            abort(403);
        }

        $data = $request->validate([
            'first_name' => ['required', 'string', 'max:50'],
            'middle_name' => ['nullable', 'string', 'max:50'],
            'last_name' => ['required', 'string', 'max:50'],
            'email' => ['nullable', 'email', 'max:100', 'unique:employees,email,' . $employee->id . ',id'],
            'phone' => ['nullable', 'string', 'max:20'],
            'work_email' => ['nullable', 'email', 'max:100'],
            'work_phone' => ['nullable', 'string', 'max:20'],
            'work_mobile' => ['nullable', 'string', 'max:20'],
            'bank_account_no' => ['nullable', 'string', 'max:50'],
            'sss_no' => ['nullable', 'string', 'max:50'],
            'philhealth_no' => ['nullable', 'string', 'max:50'],
            'hdmf_no' => ['nullable', 'string', 'max:50'],
            'tax_id_no' => ['nullable', 'string', 'max:50'],
            'department_id' => ['nullable', 'integer', 'exists:departments,id'],
            'position' => ['nullable', 'string', 'max:100'],
            'manager_id' => ['nullable', 'integer', 'exists:employees,id'],
            'contract_start_date' => ['nullable', 'date'],
            'contract_end_date' => ['nullable', 'date', 'after_or_equal:contract_start_date'],
            'working_schedule' => ['nullable', 'string', 'max:100'],
            'minimum_wage_earner' => ['nullable', 'boolean'],
            'salary_structure_type' => ['nullable', 'in:daily,monthly'],
            'contract_type' => ['nullable', 'in:Permanent,Temporary,Seasonal,Full-Time,Part-Time,Rank and File,Executive'],
            'salary_schedule_pay' => ['nullable', 'string', 'max:50'],
            'salary_structure' => ['nullable', 'in:Base for Monthly structures,Base for Monthly structures - First Cut-Off,Base for Monthly structures - Second Cut-Off,Base for Daily structures,Base for 13th Month Pay Structure'],
            'daily_rate' => ['nullable', 'numeric', 'min:0'],
            'wage' => ['nullable', 'numeric', 'min:0'],
            'hourly_rate' => ['nullable', 'numeric', 'min:0'],
            'hourly_rate_overtime' => ['nullable', 'numeric', 'min:0'],
            'sss_deduction' => ['nullable', 'numeric', 'min:0'],
            'pagibig_deduction' => ['nullable', 'numeric', 'min:0'],
            'philhealth_deduction' => ['nullable', 'numeric', 'min:0'],
            'cash_advance_deduction' => ['nullable', 'numeric', 'min:0'],
        ]);

        foreach ([
            'daily_rate',
            'wage',
            'hourly_rate',
            'hourly_rate_overtime',
            'sss_deduction',
            'pagibig_deduction',
            'philhealth_deduction',
            'cash_advance_deduction',
        ] as $field) {
            if (!array_key_exists($field, $data) || $data[$field] === null || $data[$field] === '') {
                $data[$field] = 0;
            }
        }

        if (!array_key_exists('minimum_wage_earner', $data) || $data['minimum_wage_earner'] === null || $data['minimum_wage_earner'] === '') {
            $data['minimum_wage_earner'] = false;
        }

        if (array_key_exists('wage', $data) && $data['wage'] !== null) {
            $wage = (float) $data['wage'];

            $salaryStructureType = (string) ($data['salary_structure_type'] ?? 'daily');
            if ($salaryStructureType === '') {
                $salaryStructureType = 'daily';
            }

            if ($salaryStructureType === 'monthly') {
                $daily = $wage / 22;
                $data['daily_rate'] = $daily;
                $data['hourly_rate'] = $daily / 8;
            } else {
                $data['daily_rate'] = $wage;
                $data['hourly_rate'] = $wage / 8;
            }
        }

        if (($data['manager_id'] ?? null) !== null && (int) $data['manager_id'] === (int) $employee->id) {
            return $this->redirectToEmployeesIndex($request)
                ->withErrors(['manager_id' => 'Manager cannot be the same employee.'])
                ->withInput();
        }

        $employee->update($data);

        return $this->redirectToEmployeesIndex($request)->with('status', 'employee-updated');
    }

    public function destroy(Request $request, Employee $employee)
    {
        $user = $request->user();
        if (!$user?->canManageBackoffice()) {
            abort(403);
        }

        DB::transaction(function () use ($employee) {
            $employee->roles()->detach();
            $employee->subordinates()->update(['manager_id' => null]);
            $employee->delete();
        });

        return $this->redirectToEmployeesIndex($request)->with('status', 'employee-deleted');
    }
}
