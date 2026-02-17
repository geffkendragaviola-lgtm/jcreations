<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('Employee') }}</h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="flex flex-col lg:flex-row gap-6" x-data="{ tab: 'general' }">
                <aside class="w-full lg:w-64">
                    <div class="bg-white shadow-sm rounded-lg border border-gray-200 overflow-hidden">
                        <div class="px-4 py-4 border-b bg-gray-50">
                            <div class="text-sm font-semibold text-gray-900">Employee Profile</div>
                        </div>

                        <div class="p-4 border-b">
                            <div class="flex items-center gap-3">
                                <div class="w-12 h-12 rounded-full bg-gray-100 border border-gray-200 flex items-center justify-center text-gray-600 font-semibold">
                                    {{ strtoupper(substr((string) ($employee->first_name ?? 'E'), 0, 1)) }}{{ strtoupper(substr((string) ($employee->last_name ?? 'E'), 0, 1)) }}
                                </div>
                                <div class="min-w-0">
                                    <div class="truncate text-sm font-semibold text-gray-900">{{ $employee->full_name }}</div>
                                    <div class="text-xs text-gray-500">{{ $employee->position ?? '-' }}</div>
                                </div>
                            </div>
                        </div>

                        <nav class="p-2">
                            @php
                                $navItems = [
                                    [
                                        'label' => 'Employee List',
                                        'route' => 'employees.index',
                                    ],
                                    [
                                        'label' => 'Users',
                                        'route' => 'users.index',
                                    ],
                                    [
                                        'label' => 'Incomplete Employment',
                                        'route' => 'employees.incompleteEmployment',
                                    ],
                                    [
                                        'label' => 'Incomplete Compensation',
                                        'route' => 'employees.incompleteCompensation',
                                    ],
                                    [
                                        'label' => 'Incomplete Profile',
                                        'route' => 'employees.incompleteProfile',
                                    ],
                                    [
                                        'label' => 'Incomplete Government Info',
                                        'route' => 'employees.incompleteGovernmentInfo',
                                    ],
                                    [
                                        'label' => 'Disciplinary Actions',
                                        'route' => 'employees.disciplinaryActions',
                                    ],
                                    [
                                        'label' => 'Employee Leave Credits',
                                        'route' => null,
                                    ],
                                    [
                                        'label' => 'Evaluation',
                                        'route' => null,
                                    ],
                                ];
                            @endphp

                            @foreach ($navItems as $item)
                                @if (!empty($item['route']))
                                    <a href="{{ route($item['route']) }}" class="block px-3 py-2 rounded-md text-sm text-gray-700 hover:bg-gray-50">
                                        {{ $item['label'] }}
                                    </a>
                                @else
                                    <div class="px-3 py-2 rounded-md text-sm text-gray-400 cursor-not-allowed">
                                        {{ $item['label'] }}
                                    </div>
                                @endif
                            @endforeach
                        </nav>
                    </div>
                </aside>

                <main class="flex-1">
                    <div class="bg-white shadow-sm rounded-lg border border-gray-200 overflow-hidden">
                        <div class="p-5 border-b">
                            <div class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
                                <div>
                                    <div class="text-sm text-gray-500">Employee Profile</div>
                                    <div class="text-lg font-semibold text-gray-900">{{ $employee->employee_code }} - {{ $employee->full_name }}</div>
                                    <div class="text-sm text-gray-600">{{ $employee->department?->name ?? '-' }}{{ $employee->position ? ' • ' . $employee->position : '' }}</div>
                                </div>

                                <div class="flex items-center gap-2">
                                    <a href="{{ route('employees.index') }}" class="px-4 py-2 rounded-md bg-gray-100 text-gray-800 hover:bg-gray-200">Back</a>
                                </div>
                            </div>

                            <div class="mt-4 border-b">
                                <div class="flex flex-wrap gap-6 text-sm">
                                    <button type="button" class="pb-3 -mb-px border-b-2" :class="tab === 'general' ? 'border-indigo-600 text-indigo-700 font-semibold' : 'border-transparent text-gray-600 hover:text-gray-800'" x-on:click="tab = 'general'">General Information</button>
                                    <button type="button" class="pb-3 -mb-px border-b-2" :class="tab === 'government' ? 'border-indigo-600 text-indigo-700 font-semibold' : 'border-transparent text-gray-600 hover:text-gray-800'" x-on:click="tab = 'government'">Government Information</button>
                                    <button type="button" class="pb-3 -mb-px border-b-2" :class="tab === 'contact' ? 'border-indigo-600 text-indigo-700 font-semibold' : 'border-transparent text-gray-600 hover:text-gray-800'" x-on:click="tab = 'contact'">Contact Details</button>
                                    <button type="button" class="pb-3 -mb-px border-b-2" :class="tab === 'dependents' ? 'border-indigo-600 text-indigo-700 font-semibold' : 'border-transparent text-gray-600 hover:text-gray-800'" x-on:click="tab = 'dependents'">Dependents</button>
                                    <button type="button" class="pb-3 -mb-px border-b-2" :class="tab === 'banks' ? 'border-indigo-600 text-indigo-700 font-semibold' : 'border-transparent text-gray-600 hover:text-gray-800'" x-on:click="tab = 'banks'">Banks</button>
                                </div>
                            </div>
                        </div>

                        <div class="p-5 text-gray-900">
                            <div x-show="tab === 'general'" x-cloak class="space-y-6">
                                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                    <div class="rounded-lg border border-gray-200 bg-gray-50 p-4">
                                        <div class="text-xs text-gray-500">Employee Code</div>
                                        <div class="mt-1 font-semibold text-gray-900">{{ $employee->employee_code }}</div>
                                    </div>
                                    <div class="rounded-lg border border-gray-200 p-4">
                                        <div class="text-xs text-gray-500">Department</div>
                                        <div class="mt-1 font-semibold text-gray-900">{{ $employee->department?->name ?? '-' }}</div>
                                    </div>
                                    <div class="rounded-lg border border-gray-200 p-4">
                                        <div class="text-xs text-gray-500">Manager</div>
                                        <div class="mt-1 font-semibold text-gray-900">{{ $employee->manager?->full_name ?? '-' }}</div>
                                    </div>
                                </div>

                                <div class="rounded-lg border border-gray-200 p-4">
                                    <div class="text-sm font-semibold text-gray-900">Contract Details</div>
                                    <div class="mt-3 grid grid-cols-1 md:grid-cols-3 gap-4 text-sm">
                                        <div>
                                            <div class="text-xs text-gray-500">Contract Start Date</div>
                                            <div class="mt-1 font-semibold">{{ optional($employee->contract_start_date)->format('Y-m-d') ?? '-' }}</div>
                                        </div>
                                        <div>
                                            <div class="text-xs text-gray-500">Contract End Date</div>
                                            <div class="mt-1 font-semibold">{{ optional($employee->contract_end_date)->format('Y-m-d') ?? '-' }}</div>
                                        </div>
                                        <div>
                                            <div class="text-xs text-gray-500">Contract Type</div>
                                            <div class="mt-1 font-semibold">{{ $employee->contract_type ?? '-' }}</div>
                                        </div>
                                        <div>
                                            <div class="text-xs text-gray-500">Working Schedule</div>
                                            <div class="mt-1 font-semibold">{{ $employee->working_schedule ?? '-' }}</div>
                                        </div>
                                        <div>
                                            <div class="text-xs text-gray-500">Minimum Wage Earner</div>
                                            <div class="mt-1 font-semibold">{{ $employee->minimum_wage_earner ? 'Yes' : 'No' }}</div>
                                        </div>
                                        <div>
                                            <div class="text-xs text-gray-500">Salary Scheduled Pay</div>
                                            <div class="mt-1 font-semibold">{{ $employee->salary_schedule_pay ?? '-' }}</div>
                                        </div>
                                    </div>
                                </div>

                                <div class="rounded-lg border border-gray-200 p-4">
                                    <div class="text-sm font-semibold text-gray-900">Salary Information (8 hrs)</div>
                                    <div class="mt-3 grid grid-cols-1 md:grid-cols-4 gap-4 text-sm">
                                        <div>
                                            <div class="text-xs text-gray-500">Wage (Daily)</div>
                                            <div class="mt-1 font-semibold">{{ number_format((float) ($employee->wage ?? 0), 2) }}</div>
                                        </div>
                                        <div>
                                            <div class="text-xs text-gray-500">Daily Rate</div>
                                            <div class="mt-1 font-semibold">{{ number_format((float) ($employee->daily_rate ?? 0), 2) }}</div>
                                        </div>
                                        <div>
                                            <div class="text-xs text-gray-500">Hourly Rate</div>
                                            <div class="mt-1 font-semibold">{{ number_format((float) ($employee->hourly_rate ?? 0), 4) }}</div>
                                        </div>
                                        <div>
                                            <div class="text-xs text-gray-500">Hourly Rate Overtime</div>
                                            <div class="mt-1 font-semibold">{{ number_format((float) ($employee->hourly_rate_overtime ?? 0), 4) }}</div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div x-show="tab === 'government'" x-cloak class="space-y-4">
                                <div class="rounded-lg border border-gray-200 p-4">
                                    <div class="text-sm font-semibold text-gray-900">Government Mandated Benefits</div>
                                    <div class="mt-3 grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                                        <div>
                                            <div class="text-xs text-gray-500">SSS No.</div>
                                            <div class="mt-1 font-semibold">{{ $employee->sss_no ?? '-' }}</div>
                                        </div>
                                        <div>
                                            <div class="text-xs text-gray-500">PhilHealth No.</div>
                                            <div class="mt-1 font-semibold">{{ $employee->philhealth_no ?? '-' }}</div>
                                        </div>
                                        <div>
                                            <div class="text-xs text-gray-500">HDMF No.</div>
                                            <div class="mt-1 font-semibold">{{ $employee->hdmf_no ?? '-' }}</div>
                                        </div>
                                        <div>
                                            <div class="text-xs text-gray-500">Tax ID No.</div>
                                            <div class="mt-1 font-semibold">{{ $employee->tax_id_no ?? '-' }}</div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div x-show="tab === 'contact'" x-cloak class="space-y-4">
                                <div class="rounded-lg border border-gray-200 p-4">
                                    <div class="text-sm font-semibold text-gray-900">Private Details</div>
                                    <div class="mt-3 grid grid-cols-1 md:grid-cols-3 gap-4 text-sm">
                                        <div>
                                            <div class="text-xs text-gray-500">Email</div>
                                            <div class="mt-1 font-semibold">{{ $employee->email ?? '-' }}</div>
                                        </div>
                                        <div>
                                            <div class="text-xs text-gray-500">Phone</div>
                                            <div class="mt-1 font-semibold">{{ $employee->phone ?? '-' }}</div>
                                        </div>
                                        <div>
                                            <div class="text-xs text-gray-500">Bank Acc</div>
                                            <div class="mt-1 font-semibold">{{ $employee->bank_account_no ?? '-' }}</div>
                                        </div>
                                    </div>
                                </div>

                                <div class="rounded-lg border border-gray-200 p-4">
                                    <div class="text-sm font-semibold text-gray-900">Work Details</div>
                                    <div class="mt-3 grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                                        <div>
                                            <div class="text-xs text-gray-500">Work Email</div>
                                            <div class="mt-1 font-semibold">{{ $employee->work_email ?? '-' }}</div>
                                        </div>
                                        <div>
                                            <div class="text-xs text-gray-500">Work Phone</div>
                                            <div class="mt-1 font-semibold">{{ $employee->work_phone ?? '-' }}</div>
                                        </div>
                                        <div>
                                            <div class="text-xs text-gray-500">Work Mobile</div>
                                            <div class="mt-1 font-semibold">{{ $employee->work_mobile ?? '-' }}</div>
                                        </div>
                                        <div>
                                            <div class="text-xs text-gray-500">Position</div>
                                            <div class="mt-1 font-semibold">{{ $employee->position ?? '-' }}</div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div x-show="tab === 'dependents'" x-cloak class="text-sm text-gray-600">
                                No data.
                            </div>

                            <div x-show="tab === 'banks'" x-cloak class="text-sm text-gray-600">
                                No data.
                            </div>
                        </div>
                    </div>
                </main>
            </div>
        </div>
    </div>
</x-app-layout>
