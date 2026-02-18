<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('Employee') }}</h2>
    </x-slot>

    @php $activeNav = $activeNav ?? 'employees.index'; @endphp

    <x-slot name="headerNav">
        @php
            $empNavItems = [
                ['label' => 'Employee List', 'route' => 'employees.index'],
                ['label' => 'Incomplete Employment', 'route' => 'employees.incompleteEmployment'],
                ['label' => 'Incomplete Compensation', 'route' => 'employees.incompleteCompensation'],
                ['label' => 'Incomplete Profile', 'route' => 'employees.incompleteProfile'],
                ['label' => 'Incomplete Government Info', 'route' => 'employees.incompleteGovernmentInfo'],
                ['label' => 'Disciplinary Actions', 'route' => 'employees.disciplinaryActions'],
                ['label' => 'Employee Leave Credits', 'route' => null],
                ['label' => 'Evaluation', 'route' => null],
            ];
        @endphp
        <nav class="flex gap-6 -mb-px overflow-x-auto">
            @foreach ($empNavItems as $item)
                @if (!empty($item['route']))
                    <a href="{{ route($item['route']) }}" class="inline-flex items-center whitespace-nowrap px-1 py-3 border-b-2 text-sm font-medium {{ $activeNav === $item['route'] ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">{{ $item['label'] }}</a>
                @else
                    <span class="inline-flex items-center whitespace-nowrap px-1 py-3 border-b-2 border-transparent text-sm font-medium text-gray-400 cursor-not-allowed">{{ $item['label'] }}</span>
                @endif
            @endforeach
        </nav>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                    <div class="bg-white shadow-sm rounded-xl border border-gray-200 overflow-hidden">
                        <div class="px-6 py-5 border-b border-gray-200">
                            <div class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-lg bg-indigo-100 flex items-center justify-center shrink-0">
                                        <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                                    </div>
                                    <div>
                                        <h3 class="text-lg font-bold text-gray-900">{{ $pageTitle ?? 'Employee List' }}</h3>
                                        <p class="text-xs text-gray-500 mt-0.5">Manage your employees and their information</p>
                                    </div>
                                </div>

                                <a href="{{ route('employees.create') }}" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-lg bg-indigo-600 text-white text-sm font-semibold shadow-sm hover:bg-indigo-700 transition">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                                    Add Employee
                                </a>
                            </div>

                            @php
                                $listRoute = $activeNav ?? 'employees.index';
                            @endphp

                            <form method="GET" action="{{ route($listRoute) }}" class="mt-4 grid grid-cols-1 md:grid-cols-12 gap-3 items-end">
                                <div class="md:col-span-5">
                                    <label class="text-xs font-medium text-gray-600 mb-1 block">Search</label>
                                    <div class="relative">
                                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                            <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                                        </div>
                                        <input name="search" type="text" value="{{ request('search') }}" placeholder="Search by code, first name, or last name" class="pl-9 border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-lg shadow-sm w-full text-sm" />
                                    </div>
                                </div>

                                <div class="md:col-span-4">
                                    <label class="text-xs font-medium text-gray-600 mb-1 block">Department</label>
                                    <select name="department_id" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-lg shadow-sm w-full text-sm">
                                        <option value="">All Departments</option>
                                        @foreach ($departments as $d)
                                            <option value="{{ $d->id }}" @selected((string) request('department_id') === (string) $d->id)>{{ $d->name }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="md:col-span-3 flex items-center gap-2">
                                    <button type="submit" class="flex-1 inline-flex items-center justify-center gap-1.5 px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-lg hover:bg-indigo-700 transition">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/></svg>
                                        Filter
                                    </button>
                                    <a href="{{ route($listRoute) }}" class="flex-1 inline-flex items-center justify-center px-4 py-2 bg-white border border-gray-300 text-gray-700 text-sm font-medium rounded-lg hover:bg-gray-50 transition text-center">Reset</a>
                                </div>
                            </form>
                        </div>

                        <div class="px-6 py-5 text-gray-900">

                    @if (session('status') === 'employee-updated')
                        <div class="mb-4 px-4 py-3 rounded-lg bg-emerald-50 text-emerald-700 border border-emerald-200 flex items-center gap-2 text-sm">
                            <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            Employee updated successfully.
                        </div>
                    @endif

                    @if (session('status') === 'employee-deleted')
                        <div class="mb-4 px-4 py-3 rounded-lg bg-emerald-50 text-emerald-700 border border-emerald-200 flex items-center gap-2 text-sm">
                            <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            Employee deleted successfully.
                        </div>
                    @endif

                    @if ($errors->any())
                        <div class="mb-4 px-4 py-3 rounded-lg bg-red-50 text-red-700 border border-red-200 text-sm">
                            <div class="font-semibold flex items-center gap-2">
                                <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                Please fix the errors and try again.
                            </div>
                            <ul class="list-disc ml-8 mt-1">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <div class="overflow-x-auto rounded-lg border border-gray-200">
                        <table class="min-w-full text-sm">
                            <thead>
                                <tr class="bg-gray-50 border-b border-gray-200">
                                    <th class="text-left py-3 px-4 text-xs font-semibold text-gray-600 uppercase tracking-wider">Employee Code</th>
                                    <th class="text-left py-3 px-4 text-xs font-semibold text-gray-600 uppercase tracking-wider">Name</th>
                                    <th class="text-left py-3 px-4 text-xs font-semibold text-gray-600 uppercase tracking-wider">Department</th>
                                    <th class="text-left py-3 px-4 text-xs font-semibold text-gray-600 uppercase tracking-wider">Status</th>
                                    <th class="text-left py-3 px-4 text-xs font-semibold text-gray-600 uppercase tracking-wider">Actions</th>
                                </tr>
                            </thead>
                            <tbody id="employeesTableBody" class="divide-y divide-gray-100">
                                @foreach ($employees as $e)
                                    @php
                                        $totalDeductions = (float) ($e->sss_deduction ?? 0)
                                            + (float) ($e->pagibig_deduction ?? 0)
                                            + (float) ($e->philhealth_deduction ?? 0)
                                            + (float) ($e->cash_advance_deduction ?? 0);
                                    @endphp

                                    <tr class="hover:bg-gray-50/50 transition">
                                        <td class="py-3 px-4">
                                            <span class="inline-flex items-center px-2.5 py-1 rounded-md bg-indigo-50 text-indigo-700 font-mono font-semibold text-xs border border-indigo-200">
                                                {{ $e->employee_code }}
                                            </span>
                                        </td>
                                        <td class="py-3 px-4">
                                            <a href="{{ route('employees.show', $e) }}" class="text-gray-900 font-semibold hover:text-indigo-600 transition">
                                                {{ $e->full_name }}
                                            </a>
                                            <div class="text-xs text-gray-500 mt-0.5">{{ $e->position ?? '-' }}</div>
                                        </td>
                                        <td class="py-3 px-4 text-gray-600">{{ $e->department?->name ?? '-' }}</td>
                                        <td class="py-3 px-4">
                                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-700">
                                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                                Active
                                            </span>
                                            <div class="mt-1.5 text-xs text-gray-500">Deductions: <span class="font-medium text-gray-700">{{ number_format($totalDeductions, 2) }}</span></div>
                                        </td>
                                        <td class="py-3 px-4">
                                            <div class="flex items-center gap-1.5">
                                                <button type="button" class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-indigo-50 text-indigo-600 hover:bg-indigo-100 transition" title="Edit" x-data x-on:click.prevent="$dispatch('open-modal', 'edit-employee-{{ $e->id }}')">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                                </button>
                                                <a href="{{ route('employees.show', $e) }}" class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-gray-100 text-gray-600 hover:bg-gray-200 transition" title="View Profile">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                                </a>
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
