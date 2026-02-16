<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between gap-3">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Employees') }}
            </h2>

            <form method="GET" action="{{ route('employees.index') }}" class="w-full max-w-3xl flex flex-col md:flex-row md:items-center gap-2">
                <div class="w-full">
                    <input name="search" type="text" value="{{ request('search') }}" placeholder="Search by code / first name / last name" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm w-full" />
                </div>

                <div class="w-full md:w-64">
                    <select name="department_id" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm w-full">
                        <option value="">All Departments</option>
                        @foreach ($departments as $d)
                            <option value="{{ $d->id }}" @selected((string) request('department_id') === (string) $d->id)>{{ $d->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="flex items-center gap-2">
                    <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded hover:bg-indigo-700">Filter</button>
                    <a href="{{ route('employees.index') }}" class="px-4 py-2 bg-gray-100 text-gray-800 rounded hover:bg-gray-200">Reset</a>
                </div>
            </form>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">

                    @if (session('status') === 'employee-updated')
                        <div class="mb-4 p-3 rounded bg-green-50 text-green-700 border border-green-200">
                            Employee updated.
                        </div>
                    @endif

                    @if (session('status') === 'employee-deleted')
                        <div class="mb-4 p-3 rounded bg-green-50 text-green-700 border border-green-200">
                            Employee deleted.
                        </div>
                    @endif

                    @if ($errors->any())
                        <div class="mb-4 p-3 rounded bg-red-50 text-red-700 border border-red-200">
                            <div class="font-semibold">Please fix the errors and try again.</div>
                            <ul class="list-disc ml-6">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <div class="overflow-x-auto">
                        <table class="min-w-full text-sm">
                            <thead class="bg-gray-50">
                                <tr class="border-b">
                                    <th class="text-left py-3 px-2">Code</th>
                                    <th class="text-left py-3 px-2">Name</th>
                                    <th class="text-left py-3 px-2">Department</th>
                                    <th class="text-left py-3 px-2">Job Position</th>
                                    <th class="text-left py-3 px-2">Daily Rate</th>
                                    <th class="text-left py-3 px-2">Total Deductions</th>
                                    <th class="text-left py-3 px-2">Action</th>
                                </tr>
                            </thead>
                            <tbody id="employeesTableBody">
                                @foreach ($employees as $e)
                                    @php
                                        $totalDeductions = (float) ($e->sss_deduction ?? 0)
                                            + (float) ($e->pagibig_deduction ?? 0)
                                            + (float) ($e->philhealth_deduction ?? 0)
                                            + (float) ($e->cash_advance_deduction ?? 0);
                                    @endphp

                                    <tr class="border-b">
                                        <td class="py-3 px-2 font-medium">{{ $e->employee_code }}</td>
                                        <td class="py-3 px-2">
                                            <a href="{{ route('employees.show', $e) }}" class="text-indigo-700 hover:underline">
                                                {{ $e->full_name }}
                                            </a>
                                        </td>
                                        <td class="py-3 px-2">{{ $e->department?->name ?? '-' }}</td>
                                        <td class="py-3 px-2">{{ $e->position ?? '-' }}</td>
                                        <td class="py-3 px-2">{{ number_format((float) ($e->daily_rate ?? 0), 2) }}</td>
                                        <td class="py-3 px-2">{{ number_format($totalDeductions, 2) }}</td>
                                        <td class="py-3 px-2">
                                            <div class="flex items-center gap-2">
                                                <x-secondary-button x-data="" x-on:click.prevent="$dispatch('open-modal', 'edit-employee-{{ $e->id }}')">Edit</x-secondary-button>
                                            </div>

                                            <x-modal name="edit-employee-{{ $e->id }}" :show="false" focusable>
                                                <form method="POST" action="{{ route('employees.update', $e) }}" class="p-6 space-y-4">
                                                    @csrf
                                                    @method('PATCH')

                                                    <input type="hidden" name="redirect_to" value="{{ url()->full() }}" />

                                                    <div class="text-lg font-medium text-gray-900">Edit Employee</div>
                                                    <div class="text-sm text-gray-600">{{ $e->employee_code }} - {{ $e->full_name }}</div>

                                                    <div class="pt-2">
                                                        <div class="text-sm font-medium text-gray-900">Personal Information</div>
                                                    </div>

                                                    <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                                                        <div>
                                                            <div class="text-xs text-gray-600 mb-1">First Name</div>
                                                            <input type="text" name="first_name" value="{{ old('first_name', $e->first_name) }}" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm w-full" />
                                                        </div>
                                                        <div>
                                                            <div class="text-xs text-gray-600 mb-1">Middle Name</div>
                                                            <input type="text" name="middle_name" value="{{ old('middle_name', $e->middle_name) }}" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm w-full" />
                                                        </div>
                                                        <div>
                                                            <div class="text-xs text-gray-600 mb-1">Last Name</div>
                                                            <input type="text" name="last_name" value="{{ old('last_name', $e->last_name) }}" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm w-full" />
                                                        </div>
                                                        <div>
                                                            <div class="text-xs text-gray-600 mb-1">Email</div>
                                                            <input type="email" name="email" value="{{ old('email', $e->email) }}" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm w-full" />
                                                        </div>
                                                        <div>
                                                            <div class="text-xs text-gray-600 mb-1">Phone</div>
                                                            <input type="text" name="phone" value="{{ old('phone', $e->phone) }}" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm w-full" />
                                                        </div>

                                                        <div>
                                                            <div class="text-xs text-gray-600 mb-1">Work Email</div>
                                                            <input type="email" name="work_email" value="{{ old('work_email', $e->work_email) }}" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm w-full" />
                                                        </div>
                                                        <div>
                                                            <div class="text-xs text-gray-600 mb-1">Work Phone</div>
                                                            <input type="text" name="work_phone" value="{{ old('work_phone', $e->work_phone) }}" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm w-full" />
                                                        </div>
                                                        <div>
                                                            <div class="text-xs text-gray-600 mb-1">Work Mobile</div>
                                                            <input type="text" name="work_mobile" value="{{ old('work_mobile', $e->work_mobile) }}" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm w-full" />
                                                        </div>
                                                        <div>
                                                            <div class="text-xs text-gray-600 mb-1">Department</div>
                                                            <select name="department_id" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm w-full">
                                                                <option value="">-</option>
                                                                @foreach ($departments as $d)
                                                                    <option value="{{ $d->id }}" @selected((string) old('department_id', $e->department_id) === (string) $d->id)>{{ $d->name }}</option>
                                                                @endforeach
                                                            </select>
                                                        </div>
                                                        <div>
                                                            <div class="text-xs text-gray-600 mb-1">Job Position</div>
                                                            <input type="text" name="position" value="{{ old('position', $e->position) }}" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm w-full" />
                                                        </div>
                                                        <div>
                                                            <div class="text-xs text-gray-600 mb-1">Manager</div>
                                                            <select name="manager_id" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm w-full">
                                                                <option value="">-</option>
                                                                @foreach ($managers as $m)
                                                                    <option value="{{ $m->id }}" @selected((string) old('manager_id', $e->manager_id) === (string) $m->id)>
                                                                        {{ $m->employee_code }} - {{ $m->full_name }}
                                                                    </option>
                                                                @endforeach
                                                            </select>
                                                        </div>
                                                    </div>

                                                    <div class="border-t pt-4">
                                                        <div class="text-sm font-medium text-gray-900">Salary Details</div>
                                                    </div>

                                                    <div class="grid grid-cols-1 md:grid-cols-4 gap-3">
                                                        <div>
                                                            <div class="text-xs text-gray-600 mb-1">Salary Structure Type</div>
                                                            @php
                                                                $salaryStructureType = (string) old('salary_structure_type', $e->salary_structure_type ?: 'daily');
                                                                if ($salaryStructureType === '') {
                                                                    $salaryStructureType = 'daily';
                                                                }
                                                            @endphp
                                                            <select name="salary_structure_type" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm w-full">
                                                                <option value="daily" @selected($salaryStructureType === 'daily')>daily</option>
                                                                <option value="monthly" @selected($salaryStructureType === 'monthly')>monthly</option>
                                                            </select>
                                                        </div>
                                                        <div>
                                                            <div class="text-xs text-gray-600 mb-1">Wage</div>
                                                            <input type="number" step="0.01" name="wage" value="{{ old('wage', $e->wage ?? 0) }}" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm w-full" />
                                                        </div>
                                                        <div>
                                                            <div class="text-xs text-gray-600 mb-1">Daily Rate</div>
                                                            <input type="number" step="0.01" value="{{ old('daily_rate', $e->daily_rate ?? 0) }}" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm w-full bg-gray-50" disabled />
                                                        </div>
                                                        <div>
                                                            <div class="text-xs text-gray-600 mb-1">Hourly Rate</div>
                                                            <input type="number" step="0.0001" value="{{ old('hourly_rate', $e->hourly_rate ?? 0) }}" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm w-full bg-gray-50" disabled />
                                                        </div>
                                                        <div>
                                                            <div class="text-xs text-gray-600 mb-1">Hourly Rate Overtime</div>
                                                            <input type="number" step="0.0001" name="hourly_rate_overtime" value="{{ old('hourly_rate_overtime', $e->hourly_rate_overtime ?? 0) }}" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm w-full" />
                                                        </div>
                                                        <div>
                                                            <div class="text-xs text-gray-600 mb-1">Bank Acc</div>
                                                            <input type="text" name="bank_account_no" value="{{ old('bank_account_no', $e->bank_account_no) }}" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm w-full" />
                                                        </div>
                                                        <div class="md:col-span-2">
                                                            <div class="text-xs text-gray-600 mb-1">Salary Structure</div>
                                                            @php
                                                                $salaryStructure = (string) old('salary_structure', $e->salary_structure);
                                                            @endphp
                                                            <select name="salary_structure" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm w-full">
                                                                <option value="" @selected($salaryStructure === '')>-</option>
                                                                <option value="Base for Monthly structures" @selected($salaryStructure === 'Base for Monthly structures')>Base for Monthly structures</option>
                                                                <option value="Base for Monthly structures - First Cut-Off" @selected($salaryStructure === 'Base for Monthly structures - First Cut-Off')>Base for Monthly structures - First Cut-Off</option>
                                                                <option value="Base for Monthly structures - Second Cut-Off" @selected($salaryStructure === 'Base for Monthly structures - Second Cut-Off')>Base for Monthly structures - Second Cut-Off</option>
                                                                <option value="Base for Daily structures" @selected($salaryStructure === 'Base for Daily structures')>Base for Daily structures</option>
                                                                <option value="Base for 13th Month Pay Structure" @selected($salaryStructure === 'Base for 13th Month Pay Structure')>Base for 13th Month Pay Structure</option>
                                                            </select>
                                                        </div>
                                                    </div>

                                                    <div class="border-t pt-4">
                                                        <div class="text-sm font-medium text-gray-900">Government Mandated Benefits</div>
                                                    </div>

                                                    <div class="grid grid-cols-1 md:grid-cols-4 gap-3">
                                                        <div>
                                                            <div class="text-xs text-gray-600 mb-1">SSS No.</div>
                                                            <input type="text" name="sss_no" value="{{ old('sss_no', $e->sss_no) }}" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm w-full" />
                                                        </div>
                                                        <div>
                                                            <div class="text-xs text-gray-600 mb-1">PhilHealth No.</div>
                                                            <input type="text" name="philhealth_no" value="{{ old('philhealth_no', $e->philhealth_no) }}" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm w-full" />
                                                        </div>
                                                        <div>
                                                            <div class="text-xs text-gray-600 mb-1">HDMF No.</div>
                                                            <input type="text" name="hdmf_no" value="{{ old('hdmf_no', $e->hdmf_no) }}" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm w-full" />
                                                        </div>
                                                        <div>
                                                            <div class="text-xs text-gray-600 mb-1">Tax ID No.</div>
                                                            <input type="text" name="tax_id_no" value="{{ old('tax_id_no', $e->tax_id_no) }}" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm w-full" />
                                                        </div>
                                                    </div>

                                                    <div class="border-t pt-4">
                                                        <div class="text-sm font-medium text-gray-900">Contract Details</div>
                                                    </div>

                                                    <div class="grid grid-cols-1 md:grid-cols-4 gap-3">
                                                        <div>
                                                            <div class="text-xs text-gray-600 mb-1">Contract Start Date</div>
                                                            <input type="date" name="contract_start_date" value="{{ old('contract_start_date', optional($e->contract_start_date)->format('Y-m-d')) }}" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm w-full" />
                                                        </div>
                                                        <div>
                                                            <div class="text-xs text-gray-600 mb-1">Contract End Date</div>
                                                            <input type="date" name="contract_end_date" value="{{ old('contract_end_date', optional($e->contract_end_date)->format('Y-m-d')) }}" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm w-full" />
                                                        </div>

                                                        <div>
                                                            <div class="text-xs text-gray-600 mb-1">Minimum Wage Earner</div>
                                                            <select name="minimum_wage_earner" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm w-full">
                                                                <option value="0" @selected((string) old('minimum_wage_earner', (int) ($e->minimum_wage_earner ?? 0)) === '0')>No</option>
                                                                <option value="1" @selected((string) old('minimum_wage_earner', (int) ($e->minimum_wage_earner ?? 0)) === '1')>Yes</option>
                                                            </select>
                                                        </div>
                                                        <div>
                                                            <div class="text-xs text-gray-600 mb-1">Contract Type</div>
                                                            @php
                                                                $contractType = (string) old('contract_type', $e->contract_type);
                                                            @endphp
                                                            <select name="contract_type" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm w-full">
                                                                <option value="" @selected($contractType === '')>-</option>
                                                                <option value="Permanent" @selected($contractType === 'Permanent')>Permanent</option>
                                                                <option value="Temporary" @selected($contractType === 'Temporary')>Temporary</option>
                                                                <option value="Seasonal" @selected($contractType === 'Seasonal')>Seasonal</option>
                                                                <option value="Full-Time" @selected($contractType === 'Full-Time')>Full-Time</option>
                                                                <option value="Part-Time" @selected($contractType === 'Part-Time')>Part-Time</option>
                                                                <option value="Rank and File" @selected($contractType === 'Rank and File')>Rank and File</option>
                                                                <option value="Executive" @selected($contractType === 'Executive')>Executive</option>
                                                            </select>
                                                        </div>
                                                        <div>
                                                            <div class="text-xs text-gray-600 mb-1">Salary Scheduled Pay</div>
                                                            <select name="salary_schedule_pay" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm w-full">
                                                                @php
                                                                    $salarySchedule = (string) old('salary_schedule_pay', $e->salary_schedule_pay);
                                                                @endphp
                                                                <option value="" @selected($salarySchedule === '')>-</option>
                                                                <option value="Daily" @selected($salarySchedule === 'Daily')>Daily</option>
                                                                <option value="Weekly" @selected($salarySchedule === 'Weekly')>Weekly</option>
                                                                <option value="Bi-weekly" @selected($salarySchedule === 'Bi-weekly')>Bi-weekly</option>
                                                                <option value="Bi-monthly" @selected($salarySchedule === 'Bi-monthly')>Bi-monthly</option>
                                                                <option value="Monthly" @selected($salarySchedule === 'Monthly')>Monthly</option>
                                                                <option value="Quarterly" @selected($salarySchedule === 'Quarterly')>Quarterly</option>
                                                                <option value="Semi-annually" @selected($salarySchedule === 'Semi-annually')>Semi-annually</option>
                                                                <option value="Annually" @selected($salarySchedule === 'Annually')>Annually</option>
                                                            </select>
                                                        </div>
                                                    </div>

                                                    <div class="border-t pt-4">
                                                        <div class="text-sm font-medium text-gray-900">Deductions</div>
                                                        <div class="text-xs text-gray-600">Gov is computed from SSS + Pag-IBIG + PhilHealth.</div>
                                                    </div>

                                                    <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                                                        <div>
                                                            <div class="text-xs text-gray-600 mb-1">SSS</div>
                                                            <input type="number" step="0.01" name="sss_deduction" value="{{ old('sss_deduction', $e->sss_deduction ?? 0) }}" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm w-full" />
                                                        </div>
                                                        <div>
                                                            <div class="text-xs text-gray-600 mb-1">Pag-IBIG</div>
                                                            <input type="number" step="0.01" name="pagibig_deduction" value="{{ old('pagibig_deduction', $e->pagibig_deduction ?? 0) }}" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm w-full" />
                                                        </div>
                                                        <div>
                                                            <div class="text-xs text-gray-600 mb-1">PhilHealth</div>
                                                            <input type="number" step="0.01" name="philhealth_deduction" value="{{ old('philhealth_deduction', $e->philhealth_deduction ?? 0) }}" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm w-full" />
                                                        </div>
                                                        <div>
                                                            <div class="text-xs text-gray-600 mb-1">Cash Advance</div>
                                                            <input type="number" step="0.01" name="cash_advance_deduction" value="{{ old('cash_advance_deduction', $e->cash_advance_deduction ?? 0) }}" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm w-full" />
                                                        </div>
                                                    </div>

                                                    <div class="mt-6 flex justify-end gap-2">
                                                        <x-secondary-button x-on:click="$dispatch('close-modal', 'edit-employee-{{ $e->id }}')">Cancel</x-secondary-button>
                                                        <x-primary-button>Save</x-primary-button>
                                                    </div>
                                                </form>

                                                <div class="border-t pt-4">
                                                    <div class="text-sm font-medium text-gray-900">Delete</div>
                                                    <div class="text-xs text-gray-600">This will remove {{ $e->employee_code }} - {{ $e->full_name }}.</div>

                                                    <div class="mt-3">
                                                        <form method="POST" action="{{ route('employees.destroy', $e) }}">
                                                            @csrf
                                                            @method('DELETE')
                                                            <input type="hidden" name="redirect_to" value="{{ url()->full() }}" />
                                                            <x-danger-button>Delete Employee</x-danger-button>
                                                        </form>
                                                    </div>
                                                </div>
                                            </x-modal>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-4">
                        {{ $employees->links() }}
                    </div>

                </div>
            </div>
        </div>
    </div>
</x-app-layout>
