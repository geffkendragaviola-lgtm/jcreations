<?php

namespace App\Http\Controllers;

use App\Models\Department;
use App\Models\Employee;
use Illuminate\Http\Request;

class EmployeeManagementController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        if (!$user?->isAdmin()) {
            abort(403);
        }

        $employees = Employee::query()
            ->with(['department', 'manager'])
            ->orderBy('employee_code')
            ->get();

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

    public function update(Request $request, Employee $employee)
    {
        $user = $request->user();
        if (!$user?->isAdmin()) {
            abort(403);
        }

        $data = $request->validate([
            'employee_code' => ['required', 'string', 'max:50', 'unique:employees,employee_code,' . $employee->id . ',id'],
            'first_name' => ['required', 'string', 'max:50'],
            'middle_name' => ['nullable', 'string', 'max:50'],
            'last_name' => ['required', 'string', 'max:50'],
            'email' => ['nullable', 'email', 'max:100', 'unique:employees,email,' . $employee->id . ',id'],
            'phone' => ['nullable', 'string', 'max:20'],
            'department_id' => ['nullable', 'integer', 'exists:departments,id'],
            'position' => ['nullable', 'string', 'max:100'],
            'manager_id' => ['nullable', 'integer', 'exists:employees,id'],
            'daily_rate' => ['nullable', 'numeric', 'min:0'],
            'government_deduction' => ['nullable', 'numeric', 'min:0'],
            'sss_deduction' => ['nullable', 'numeric', 'min:0'],
            'pagibig_deduction' => ['nullable', 'numeric', 'min:0'],
            'philhealth_deduction' => ['nullable', 'numeric', 'min:0'],
            'cash_advance_deduction' => ['nullable', 'numeric', 'min:0'],
        ]);

        if (($data['manager_id'] ?? null) !== null && (int) $data['manager_id'] === (int) $employee->id) {
            return back()->withErrors(['manager_id' => 'Manager cannot be the same employee.']);
        }

        $employee->update($data);

        return back()->with('status', 'employee-updated');
    }
}
