<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('Leave Balances') }}</h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            @if (session('status') === 'balance-created')
                <div class="p-3 rounded bg-green-50 text-green-700 border border-green-200">Balance created.</div>
            @endif
            @if (session('status') === 'balance-updated')
                <div class="p-3 rounded bg-green-50 text-green-700 border border-green-200">Balance updated.</div>
            @endif
            @if (session('status'))
                @if (is_string(session('status')) && str_starts_with(session('status'), 'Created '))
                    <div class="p-3 rounded bg-green-50 text-green-700 border border-green-200">{{ session('status') }}</div>
                @endif
            @endif

            @if ($errors->any())
                <div class="p-3 rounded bg-red-50 text-red-700 border border-red-200">
                    <div class="font-semibold">Please fix the errors and try again.</div>
                    <ul class="list-disc ml-6">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="bg-white shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 space-y-6">
                    <form method="GET" class="grid grid-cols-1 md:grid-cols-6 gap-3 items-end">
                        <div>
                            <x-input-label for="year" :value="__('Year')" />
                            <x-text-input id="year" name="year" type="number" min="2020" max="2099" class="mt-1 block w-full" value="{{ $year }}" />
                        </div>
                        <div class="md:col-span-3">
                            <x-input-label for="search" :value="__('Search Employee')" />
                            <x-text-input id="search" name="search" type="text" class="mt-1 block w-full" value="{{ $filters['search'] ?? '' }}" placeholder="Employee code or name" />
                        </div>
                        <div>
                            <x-primary-button>Filter</x-primary-button>
                        </div>
                    </form>

                    <div class="border-t pt-6">
                        <div class="text-lg font-semibold">Add Leave Balance</div>
                        <form method="POST" action="{{ route('leave-balances.store') }}" class="mt-3 grid grid-cols-1 md:grid-cols-6 gap-3 items-end">
                            @csrf
                            <div class="md:col-span-2">
                                <x-input-label for="employee_id" :value="__('Employee')" />
                                <select id="employee_id" name="employee_id" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" required>
                                    <option value="">Select...</option>
                                    @foreach ($employees as $e)
                                        <option value="{{ $e->id }}">{{ $e->employee_code }} - {{ $e->full_name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <x-input-label for="leave_type" :value="__('Leave Type')" />
                                <x-text-input id="leave_type" name="leave_type" type="text" class="mt-1 block w-full" required />
                            </div>
                            <div>
                                <x-input-label for="total_credits" :value="__('Total Credits (days)')" />
                                <x-text-input id="total_credits" name="total_credits" type="number" min="0" step="0.5" class="mt-1 block w-full" required />
                            </div>
                            <div>
                                <x-input-label for="year_add" :value="__('Year')" />
                                <x-text-input id="year_add" name="year" type="number" min="2020" max="2099" class="mt-1 block w-full" value="{{ $year }}" required />
                            </div>
                            <div class="md:col-span-6 flex justify-end">
                                <x-primary-button>Add</x-primary-button>
                            </div>
                        </form>
                    </div>

                    <div class="border-t pt-6">
                        <div class="text-lg font-semibold">Bulk Create</div>
                        <form method="POST" action="{{ route('leave-balances.bulk-create') }}" class="mt-3 grid grid-cols-1 md:grid-cols-6 gap-3 items-end">
                            @csrf
                            <div class="md:col-span-2">
                                <x-input-label for="bulk_leave_type" :value="__('Leave Type')" />
                                <x-text-input id="bulk_leave_type" name="leave_type" type="text" class="mt-1 block w-full" required />
                            </div>
                            <div>
                                <x-input-label for="bulk_total_credits" :value="__('Total Credits (days)')" />
                                <x-text-input id="bulk_total_credits" name="total_credits" type="number" min="0" step="0.5" class="mt-1 block w-full" required />
                            </div>
                            <div>
                                <x-input-label for="bulk_year" :value="__('Year')" />
                                <x-text-input id="bulk_year" name="year" type="number" min="2020" max="2099" class="mt-1 block w-full" value="{{ $year }}" required />
                            </div>
                            <div class="md:col-span-6 flex justify-end">
                                <x-primary-button>Create for all active employees</x-primary-button>
                            </div>
                        </form>
                    </div>

                    <div class="border-t pt-6 overflow-x-auto">
                        <table class="min-w-full text-sm">
                            <thead>
                                <tr class="border-b">
                                    <th class="text-left py-2">Employee</th>
                                    <th class="text-left py-2">Leave Type</th>
                                    <th class="text-left py-2">Year</th>
                                    <th class="text-left py-2">Total</th>
                                    <th class="text-left py-2">Used</th>
                                    <th class="text-left py-2">Remaining</th>
                                    <th class="text-left py-2">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($balances as $b)
                                    <tr class="border-b align-top">
                                        <td class="py-2">{{ $b->employee?->employee_code }} - {{ $b->employee?->full_name }}</td>
                                        <td class="py-2">{{ $b->leave_type }}</td>
                                        <td class="py-2">{{ $b->year }}</td>
                                        <td class="py-2">{{ $b->total_credits }}</td>
                                        <td class="py-2">{{ $b->used }}</td>
                                        <td class="py-2">{{ $b->remaining }}</td>
                                        <td class="py-2">
                                            <x-secondary-button type="button" x-data x-on:click="$dispatch('open-modal', 'edit-balance-{{ $b->id }}')">Edit</x-secondary-button>

                                            <x-modal name="edit-balance-{{ $b->id }}" :show="false" focusable>
                                                <form method="POST" action="{{ route('leave-balances.update', $b) }}" class="p-6 space-y-4">
                                                    @csrf
                                                    @method('patch')

                                                    <div class="text-lg font-medium text-gray-900">Edit Leave Balance</div>
                                                    <div class="text-sm text-gray-600">{{ $b->employee?->employee_code }} - {{ $b->employee?->full_name }} ({{ $b->leave_type }})</div>

                                                    <div>
                                                        <x-input-label :value="__('Total Credits')" />
                                                        <x-text-input name="total_credits" type="number" min="0" step="0.5" class="mt-1 block w-full" value="{{ old('total_credits', $b->total_credits) }}" required />
                                                    </div>

                                                    <div class="flex justify-end gap-2">
                                                        <x-secondary-button type="button" x-on:click="$dispatch('close-modal', 'edit-balance-{{ $b->id }}')">Cancel</x-secondary-button>
                                                        <x-primary-button>Save</x-primary-button>
                                                    </div>
                                                </form>
                                            </x-modal>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="py-3 text-gray-600">No balances found.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    @if (method_exists($balances, 'links'))
                        <div>{{ $balances->links() }}</div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
