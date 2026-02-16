<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between gap-3">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Employee Details') }}
            </h2>

            <a href="{{ route('employees.index') }}" class="px-4 py-2 bg-gray-100 text-gray-800 rounded hover:bg-gray-200">Back</a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 space-y-6">
                    <div>
                        <div class="text-lg font-medium text-gray-900">{{ $employee->employee_code }} - {{ $employee->full_name }}</div>
                        <div class="text-sm text-gray-600">{{ $employee->department?->name ?? '-' }}{{ $employee->position ? ' • ' . $employee->position : '' }}</div>
                    </div>

                    <div class="border-t pt-4">
                        <div class="text-sm font-medium text-gray-900">Private Details</div>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                        <div>
                            <div class="text-xs text-gray-600">Email</div>
                            <div class="font-medium">{{ $employee->email ?? '-' }}</div>
                        </div>
                        <div>
                            <div class="text-xs text-gray-600">Phone</div>
                            <div class="font-medium">{{ $employee->phone ?? '-' }}</div>
                        </div>
                        <div>
                            <div class="text-xs text-gray-600">Bank Acc</div>
                            <div class="font-medium">{{ $employee->bank_account_no ?? '-' }}</div>
                        </div>
                    </div>

                    <div class="border-t pt-4">
                        <div class="text-sm font-medium text-gray-900">Work Details</div>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                        <div>
                            <div class="text-xs text-gray-600">Work Email</div>
                            <div class="font-medium">{{ $employee->work_email ?? '-' }}</div>
                        </div>
                        <div>
                            <div class="text-xs text-gray-600">Work Phone</div>
                            <div class="font-medium">{{ $employee->work_phone ?? '-' }}</div>
                        </div>
                        <div>
                            <div class="text-xs text-gray-600">Work Mobile</div>
                            <div class="font-medium">{{ $employee->work_mobile ?? '-' }}</div>
                        </div>
                        <div>
                            <div class="text-xs text-gray-600">Manager</div>
                            <div class="font-medium">{{ $employee->manager?->full_name ?? '-' }}</div>
                        </div>
                    </div>

                    <div class="border-t pt-4">
                        <div class="text-sm font-medium text-gray-900">Government Mandated Benefits</div>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                        <div>
                            <div class="text-xs text-gray-600">SSS No.</div>
                            <div class="font-medium">{{ $employee->sss_no ?? '-' }}</div>
                        </div>
                        <div>
                            <div class="text-xs text-gray-600">PhilHealth No.</div>
                            <div class="font-medium">{{ $employee->philhealth_no ?? '-' }}</div>
                        </div>
                        <div>
                            <div class="text-xs text-gray-600">HDMF No.</div>
                            <div class="font-medium">{{ $employee->hdmf_no ?? '-' }}</div>
                        </div>
                        <div>
                            <div class="text-xs text-gray-600">Tax ID No.</div>
                            <div class="font-medium">{{ $employee->tax_id_no ?? '-' }}</div>
                        </div>
                    </div>

                    <div class="border-t pt-4">
                        <div class="text-sm font-medium text-gray-900">Contract Details</div>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                        <div>
                            <div class="text-xs text-gray-600">Contract Start Date</div>
                            <div class="font-medium">{{ optional($employee->contract_start_date)->format('Y-m-d') ?? '-' }}</div>
                        </div>
                        <div>
                            <div class="text-xs text-gray-600">Contract End Date</div>
                            <div class="font-medium">{{ optional($employee->contract_end_date)->format('Y-m-d') ?? '-' }}</div>
                        </div>

                        <div>
                            <div class="text-xs text-gray-600">Working Schedule</div>
                            <div class="font-medium">{{ $employee->working_schedule ?? '-' }}</div>
                        </div>

                        <div>
                            <div class="text-xs text-gray-600">Minimum Wage Earner</div>
                            <div class="font-medium">{{ $employee->minimum_wage_earner ? 'Yes' : 'No' }}</div>
                        </div>
                        <div>
                            <div class="text-xs text-gray-600">Salary Structure Type</div>
                            <div class="font-medium">{{ $employee->salary_structure_type ?? '-' }}</div>
                        </div>
                        <div>
                            <div class="text-xs text-gray-600">Contract Type</div>
                            <div class="font-medium">{{ $employee->contract_type ?? '-' }}</div>
                        </div>
                        <div>
                            <div class="text-xs text-gray-600">Salary Scheduled Pay</div>
                            <div class="font-medium">{{ $employee->salary_schedule_pay ?? '-' }}</div>
                        </div>
                        <div>
                            <div class="text-xs text-gray-600">Salary Structure</div>
                            <div class="font-medium">{{ $employee->salary_structure ?? '-' }}</div>
                        </div>
                    </div>

                    <div class="border-t pt-4">
                        <div class="text-sm font-medium text-gray-900">Salary Information (8 hrs)</div>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                        <div>
                            <div class="text-xs text-gray-600">Wage (Daily)</div>
                            <div class="font-medium">{{ number_format((float) ($employee->wage ?? 0), 2) }}</div>
                        </div>
                        <div>
                            <div class="text-xs text-gray-600">Daily Rate</div>
                            <div class="font-medium">{{ number_format((float) ($employee->daily_rate ?? 0), 2) }}</div>
                        </div>
                        <div>
                            <div class="text-xs text-gray-600">Hourly Rate</div>
                            <div class="font-medium">{{ number_format((float) ($employee->hourly_rate ?? 0), 4) }}</div>
                        </div>
                        <div>
                            <div class="text-xs text-gray-600">Hourly Rate Overtime</div>
                            <div class="font-medium">{{ number_format((float) ($employee->hourly_rate_overtime ?? 0), 4) }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
