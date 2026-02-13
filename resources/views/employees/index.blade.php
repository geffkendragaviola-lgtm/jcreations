<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between gap-3">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Employees') }}
            </h2>

            <div class="w-full max-w-md">
                <input id="employeeSearch" type="text" placeholder="Search by code / name" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm w-full" />
            </div>
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
                                    <th class="text-left py-3 px-2">Position</th>
                                    <th class="text-left py-3 px-2">Daily Rate</th>
                                    <th class="text-left py-3 px-2">Total Deductions</th>
                                    <th class="text-left py-3 px-2">Action</th>
                                </tr>
                            </thead>
                            <tbody id="employeesTableBody">
                                @foreach ($employees as $e)
                                    @php
                                        $totalDeductions = (float) ($e->government_deduction ?? 0)
                                            + (float) ($e->sss_deduction ?? 0)
                                            + (float) ($e->pagibig_deduction ?? 0)
                                            + (float) ($e->philhealth_deduction ?? 0)
                                            + (float) ($e->cash_advance_deduction ?? 0);
                                    @endphp

                                    <tr class="border-b" data-search="{{ strtolower($e->employee_code . ' ' . $e->full_name) }}">
                                        <td class="py-3 px-2 font-medium">{{ $e->employee_code }}</td>
                                        <td class="py-3 px-2">{{ $e->full_name }}</td>
                                        <td class="py-3 px-2">{{ $e->department?->name ?? '-' }}</td>
                                        <td class="py-3 px-2">{{ $e->position ?? '-' }}</td>
                                        <td class="py-3 px-2">{{ number_format((float) ($e->daily_rate ?? 0), 2) }}</td>
                                        <td class="py-3 px-2">{{ number_format($totalDeductions, 2) }}</td>
                                        <td class="py-3 px-2">
                                            <details class="group">
                                                <summary class="cursor-pointer select-none text-indigo-700 hover:text-indigo-900">
                                                    Edit
                                                </summary>

                                                <div class="mt-3 p-4 bg-gray-50 border rounded">
                                                    <form method="POST" action="{{ route('employees.update', $e) }}" class="space-y-4">
                                                        @csrf
                                                        @method('PATCH')

                                                        <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                                                            <div>
                                                                <div class="text-xs text-gray-600 mb-1">Employee Code</div>
                                                                <input type="text" name="employee_code" value="{{ old('employee_code', $e->employee_code) }}" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm w-full" />
                                                            </div>
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
                                                                <div class="text-xs text-gray-600 mb-1">Department</div>
                                                                <select name="department_id" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm w-full">
                                                                    <option value="">-</option>
                                                                    @foreach ($departments as $d)
                                                                        <option value="{{ $d->id }}" @selected((string) old('department_id', $e->department_id) === (string) $d->id)>{{ $d->name }}</option>
                                                                    @endforeach
                                                                </select>
                                                            </div>
                                                            <div>
                                                                <div class="text-xs text-gray-600 mb-1">Position</div>
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

                                                        <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                                                            <div>
                                                                <div class="text-xs text-gray-600 mb-1">Daily Rate</div>
                                                                <input type="number" step="0.01" name="daily_rate" value="{{ old('daily_rate', $e->daily_rate) }}" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm w-full" />
                                                            </div>
                                                            <div>
                                                                <div class="text-xs text-gray-600 mb-1">Government Deduction</div>
                                                                <input type="number" step="0.01" name="government_deduction" value="{{ old('government_deduction', $e->government_deduction) }}" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm w-full" />
                                                            </div>
                                                            <div>
                                                                <div class="text-xs text-gray-600 mb-1">SSS</div>
                                                                <input type="number" step="0.01" name="sss_deduction" value="{{ old('sss_deduction', $e->sss_deduction) }}" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm w-full" />
                                                            </div>
                                                            <div>
                                                                <div class="text-xs text-gray-600 mb-1">Pag-IBIG</div>
                                                                <input type="number" step="0.01" name="pagibig_deduction" value="{{ old('pagibig_deduction', $e->pagibig_deduction) }}" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm w-full" />
                                                            </div>
                                                            <div>
                                                                <div class="text-xs text-gray-600 mb-1">PhilHealth</div>
                                                                <input type="number" step="0.01" name="philhealth_deduction" value="{{ old('philhealth_deduction', $e->philhealth_deduction) }}" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm w-full" />
                                                            </div>
                                                            <div>
                                                                <div class="text-xs text-gray-600 mb-1">Cash Advance</div>
                                                                <input type="number" step="0.01" name="cash_advance_deduction" value="{{ old('cash_advance_deduction', $e->cash_advance_deduction) }}" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm w-full" />
                                                            </div>
                                                        </div>

                                                        <div class="flex items-center justify-end gap-2">
                                                            <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded hover:bg-indigo-700">Save</button>
                                                        </div>
                                                    </form>
                                                </div>
                                            </details>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <script>
                        (function () {
                            const input = document.getElementById('employeeSearch');
                            const tbody = document.getElementById('employeesTableBody');
                            if (!input || !tbody) return;

                            function applyFilter() {
                                const q = String(input.value || '').trim().toLowerCase();
                                const rows = tbody.querySelectorAll('tr[data-search]');
                                rows.forEach((tr) => {
                                    const hay = String(tr.getAttribute('data-search') || '');
                                    tr.style.display = q === '' || hay.includes(q) ? '' : 'none';
                                });
                            }

                            input.addEventListener('input', applyFilter);
                        })();
                    </script>

                </div>
            </div>
        </div>
    </div>
</x-app-layout>
