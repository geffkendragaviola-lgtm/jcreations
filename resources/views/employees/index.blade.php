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
                                    <th class="text-left py-3 px-2">Position</th>
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
                                        <td class="py-3 px-2">{{ $e->full_name }}</td>
                                        <td class="py-3 px-2">{{ $e->department?->name ?? '-' }}</td>
                                        <td class="py-3 px-2">{{ $e->position ?? '-' }}</td>
                                        <td class="py-3 px-2">{{ number_format((float) ($e->daily_rate ?? 0), 2) }}</td>
                                        <td class="py-3 px-2">{{ number_format($totalDeductions, 2) }}</td>
                                        <td class="py-3 px-2">
                                            <div class="flex items-center gap-2">
                                                <x-secondary-button x-data="" x-on:click.prevent="$dispatch('open-modal', 'edit-employee-{{ $e->id }}')">Edit</x-secondary-button>
                                                <x-danger-button x-data="" x-on:click.prevent="$dispatch('open-modal', 'delete-employee-{{ $e->id }}')">Delete</x-danger-button>
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

                                                        <div>
                                                            <div class="text-xs text-gray-600 mb-1">Daily Rate</div>
                                                            <input type="number" step="0.01" name="daily_rate" value="{{ old('daily_rate', $e->daily_rate ?? 0) }}" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm w-full" />
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
                                            </x-modal>

                                            <x-modal name="delete-employee-{{ $e->id }}" :show="false" focusable>
                                                <form method="POST" action="{{ route('employees.destroy', $e) }}" class="p-6">
                                                    @csrf
                                                    @method('DELETE')

                                                    <input type="hidden" name="redirect_to" value="{{ url()->full() }}" />

                                                    <div class="text-lg font-medium text-gray-900">Delete employee?</div>
                                                    <div class="mt-1 text-sm text-gray-600">This will remove {{ $e->employee_code }} - {{ $e->full_name }}.</div>

                                                    <div class="mt-6 flex justify-end gap-2">
                                                        <x-secondary-button x-on:click="$dispatch('close-modal', 'delete-employee-{{ $e->id }}')">Cancel</x-secondary-button>
                                                        <x-danger-button>Delete</x-danger-button>
                                                    </div>
                                                </form>
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
