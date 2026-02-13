<?php

namespace App\Http\Requests;

use App\Models\Employee;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class EmployeeProfileUpdateRequest extends FormRequest
{
    public function rules(): array
    {
        $employeeId = optional($this->user())->employee_id;

        return [
            'first_name' => ['required', 'string', 'max:50'],
            'middle_name' => ['nullable', 'string', 'max:50'],
            'last_name' => ['required', 'string', 'max:50'],
            'email' => [
                'nullable',
                'string',
                'lowercase',
                'email',
                'max:100',
                Rule::unique(Employee::class, 'email')->ignore($employeeId),
            ],
            'phone' => ['nullable', 'string', 'max:20'],
            'position' => ['nullable', 'string', 'max:100'],
        ];
    }
}
