<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('Employee') }}</h2>
    </x-slot>

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
                    <a href="{{ route($item['route']) }}" class="inline-flex items-center whitespace-nowrap px-1 py-3 border-b-2 text-sm font-medium border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300">{{ $item['label'] }}</a>
                @else
                    <span class="inline-flex items-center whitespace-nowrap px-1 py-3 border-b-2 border-transparent text-sm font-medium text-gray-400 cursor-not-allowed">{{ $item['label'] }}</span>
                @endif
            @endforeach
        </nav>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8" x-data="{ tab: 'general' }">
            <div class="bg-white shadow-sm rounded-xl border border-gray-200 overflow-hidden">

                {{-- Profile Header --}}
                <div class="px-6 py-5 bg-gradient-to-r from-indigo-50 via-white to-white border-b border-gray-200">
                    <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                        <div class="flex items-center gap-4">
                            <div class="w-14 h-14 rounded-full bg-indigo-100 border-2 border-indigo-200 flex items-center justify-center text-indigo-600 font-bold text-lg shrink-0">
                                {{ strtoupper(substr((string) ($employee->first_name ?? 'E'), 0, 1)) }}{{ strtoupper(substr((string) ($employee->last_name ?? 'E'), 0, 1)) }}
                            </div>
                            <div class="min-w-0">
                                <div class="flex items-center gap-2 flex-wrap">
                                    <h3 class="text-lg font-bold text-gray-900 truncate">{{ $employee->full_name }}</h3>
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-700">Active</span>
                                </div>
                                <div class="text-sm text-gray-500 mt-0.5">
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded bg-indigo-50 text-indigo-700 font-mono font-semibold text-xs border border-indigo-200">{{ $employee->employee_code }}</span>
                                    <span class="mx-1 text-gray-300">|</span>
                                    {{ $employee->department?->name ?? '-' }}{{ $employee->position ? ' · ' . $employee->position : '' }}
                                </div>
                            </div>
                        </div>

                        <a href="{{ route('employees.index') }}" class="inline-flex items-center gap-2 px-4 py-2 rounded-lg border border-gray-300 bg-white text-gray-700 text-sm font-medium shadow-sm hover:bg-gray-50 transition">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
                            Back to List
                        </a>
                    </div>
                </div>

                {{-- Detail Tabs --}}
                <div class="px-6 border-b border-gray-200 bg-white">
                    <nav class="flex gap-6 -mb-px overflow-x-auto">
                        @php
                            $tabs = [
                                'general' => 'General Information',
                                'government' => 'Government Information',
                                'contact' => 'Contact Details',
                                'dependents' => 'Dependents',
                                'banks' => 'Banks',
                            ];
                        @endphp
                        @foreach ($tabs as $key => $label)
                            <button type="button"
                                class="whitespace-nowrap py-3 px-1 border-b-2 text-sm font-medium transition"
                                :class="tab === '{{ $key }}' ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                                x-on:click="tab = '{{ $key }}'">{{ $label }}</button>
                        @endforeach
                    </nav>
                </div>

                {{-- Tab Content --}}
                <div class="p-6 text-gray-900">

                    {{-- General --}}
                    <div x-show="tab === 'general'" x-cloak class="space-y-6">
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div class="rounded-xl border border-indigo-100 bg-indigo-50/50 p-4">
                                <div class="text-xs font-medium text-indigo-500 uppercase tracking-wide">Employee Code</div>
                                <div class="mt-1.5 text-base font-bold text-indigo-900">{{ $employee->employee_code }}</div>
                            </div>
                            <div class="rounded-xl border border-gray-200 bg-gray-50/50 p-4">
                                <div class="text-xs font-medium text-gray-500 uppercase tracking-wide">Department</div>
                                <div class="mt-1.5 text-base font-semibold text-gray-900">{{ $employee->department?->name ?? '-' }}</div>
                            </div>
                            <div class="rounded-xl border border-gray-200 bg-gray-50/50 p-4">
                                <div class="text-xs font-medium text-gray-500 uppercase tracking-wide">Manager</div>
                                <div class="mt-1.5 text-base font-semibold text-gray-900">{{ $employee->manager?->full_name ?? '-' }}</div>
                            </div>
                        </div>

                        <div class="rounded-xl border border-gray-200 p-5">
                            <h4 class="text-sm font-bold text-gray-900 flex items-center gap-2">
                                <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                Contract Details
                            </h4>
                            <div class="mt-4 grid grid-cols-1 md:grid-cols-3 gap-4 text-sm">
                                <div><div class="text-xs text-gray-500">Contract Start</div><div class="mt-1 font-semibold">{{ optional($employee->contract_start_date)->format('M d, Y') ?? '-' }}</div></div>
                                <div><div class="text-xs text-gray-500">Contract End</div><div class="mt-1 font-semibold">{{ optional($employee->contract_end_date)->format('M d, Y') ?? '-' }}</div></div>
                                <div><div class="text-xs text-gray-500">Contract Type</div><div class="mt-1 font-semibold">{{ $employee->contract_type ?? '-' }}</div></div>
                                <div><div class="text-xs text-gray-500">Working Schedule</div><div class="mt-1 font-semibold">{{ $employee->working_schedule ?? '-' }}</div></div>
                                <div><div class="text-xs text-gray-500">Minimum Wage Earner</div><div class="mt-1 font-semibold">{{ $employee->minimum_wage_earner ? 'Yes' : 'No' }}</div></div>
                                <div><div class="text-xs text-gray-500">Salary Scheduled Pay</div><div class="mt-1 font-semibold">{{ $employee->salary_schedule_pay ?? '-' }}</div></div>
                            </div>
                        </div>

                        <div class="rounded-xl border border-gray-200 p-5">
                            <h4 class="text-sm font-bold text-gray-900 flex items-center gap-2">
                                <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                Salary Information (8 hrs)
                            </h4>
                            <div class="mt-4 grid grid-cols-2 md:grid-cols-4 gap-4 text-sm">
                                <div class="bg-gray-50 rounded-lg p-3"><div class="text-xs text-gray-500">Wage (Daily)</div><div class="mt-1 font-bold text-gray-900">{{ number_format((float) ($employee->wage ?? 0), 2) }}</div></div>
                                <div class="bg-gray-50 rounded-lg p-3"><div class="text-xs text-gray-500">Daily Rate</div><div class="mt-1 font-bold text-gray-900">{{ number_format((float) ($employee->daily_rate ?? 0), 2) }}</div></div>
                                <div class="bg-gray-50 rounded-lg p-3"><div class="text-xs text-gray-500">Hourly Rate</div><div class="mt-1 font-bold text-gray-900">{{ number_format((float) ($employee->hourly_rate ?? 0), 4) }}</div></div>
                                <div class="bg-gray-50 rounded-lg p-3"><div class="text-xs text-gray-500">OT Rate</div><div class="mt-1 font-bold text-gray-900">{{ number_format((float) ($employee->hourly_rate_overtime ?? 0), 4) }}</div></div>
                            </div>
                        </div>
                    </div>

                    {{-- Government --}}
                    <div x-show="tab === 'government'" x-cloak class="space-y-4">
                        <div class="rounded-xl border border-gray-200 p-5">
                            <h4 class="text-sm font-bold text-gray-900 flex items-center gap-2">
                                <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                                Government Mandated Benefits
                            </h4>
                            <div class="mt-4 grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                                <div class="bg-gray-50 rounded-lg p-3"><div class="text-xs text-gray-500">SSS No.</div><div class="mt-1 font-semibold font-mono">{{ $employee->sss_no ?? '-' }}</div></div>
                                <div class="bg-gray-50 rounded-lg p-3"><div class="text-xs text-gray-500">PhilHealth No.</div><div class="mt-1 font-semibold font-mono">{{ $employee->philhealth_no ?? '-' }}</div></div>
                                <div class="bg-gray-50 rounded-lg p-3"><div class="text-xs text-gray-500">HDMF No.</div><div class="mt-1 font-semibold font-mono">{{ $employee->hdmf_no ?? '-' }}</div></div>
                                <div class="bg-gray-50 rounded-lg p-3"><div class="text-xs text-gray-500">Tax ID No.</div><div class="mt-1 font-semibold font-mono">{{ $employee->tax_id_no ?? '-' }}</div></div>
                            </div>
                        </div>
                    </div>

                    {{-- Contact --}}
                    <div x-show="tab === 'contact'" x-cloak class="space-y-5">
                        <div class="rounded-xl border border-gray-200 p-5">
                            <h4 class="text-sm font-bold text-gray-900 flex items-center gap-2">
                                <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                                Private Details
                            </h4>
                            <div class="mt-4 grid grid-cols-1 md:grid-cols-3 gap-4 text-sm">
                                <div class="bg-gray-50 rounded-lg p-3"><div class="text-xs text-gray-500">Email</div><div class="mt-1 font-semibold">{{ $employee->email ?? '-' }}</div></div>
                                <div class="bg-gray-50 rounded-lg p-3"><div class="text-xs text-gray-500">Phone</div><div class="mt-1 font-semibold">{{ $employee->phone ?? '-' }}</div></div>
                                <div class="bg-gray-50 rounded-lg p-3"><div class="text-xs text-gray-500">Bank Acc</div><div class="mt-1 font-semibold font-mono">{{ $employee->bank_account_no ?? '-' }}</div></div>
                            </div>
                        </div>

                        <div class="rounded-xl border border-gray-200 p-5">
                            <h4 class="text-sm font-bold text-gray-900 flex items-center gap-2">
                                <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                                Work Details
                            </h4>
                            <div class="mt-4 grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                                <div class="bg-gray-50 rounded-lg p-3"><div class="text-xs text-gray-500">Work Email</div><div class="mt-1 font-semibold">{{ $employee->work_email ?? '-' }}</div></div>
                                <div class="bg-gray-50 rounded-lg p-3"><div class="text-xs text-gray-500">Work Phone</div><div class="mt-1 font-semibold">{{ $employee->work_phone ?? '-' }}</div></div>
                                <div class="bg-gray-50 rounded-lg p-3"><div class="text-xs text-gray-500">Work Mobile</div><div class="mt-1 font-semibold">{{ $employee->work_mobile ?? '-' }}</div></div>
                                <div class="bg-gray-50 rounded-lg p-3"><div class="text-xs text-gray-500">Position</div><div class="mt-1 font-semibold">{{ $employee->position ?? '-' }}</div></div>
                            </div>
                        </div>
                    </div>

                    {{-- Dependents --}}
                    <div x-show="tab === 'dependents'" x-cloak>
                        <div class="flex flex-col items-center justify-center py-12 text-gray-400">
                            <svg class="w-12 h-12 mb-3" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                            <div class="text-sm font-medium">No dependents recorded</div>
                        </div>
                    </div>

                    {{-- Banks --}}
                    <div x-show="tab === 'banks'" x-cloak>
                        <div class="flex flex-col items-center justify-center py-12 text-gray-400">
                            <svg class="w-12 h-12 mb-3" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0115.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 013 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 00-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 01-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 003 15h-.75M15 10.5a3 3 0 11-6 0 3 3 0 016 0zm3 0h.008v.008H18V10.5zm-12 0h.008v.008H6V10.5z"/></svg>
                            <div class="text-sm font-medium">No bank accounts recorded</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
