<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Time Adjustment Requests') }}
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                <form method="GET" action="{{ route('time-adjustment-requests.index') }}" class="flex gap-2 items-end">
                    <div>
                        <x-input-label for="status" :value="__('Status')" />
                        <select id="status" name="status" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                            <option value="" {{ ($filters['status'] ?? '') === '' ? 'selected' : '' }}>All</option>
                            <option value="pending" {{ ($filters['status'] ?? '') === 'pending' ? 'selected' : '' }}>Pending</option>
                            <option value="approved" {{ ($filters['status'] ?? '') === 'approved' ? 'selected' : '' }}>Approved</option>
                            <option value="rejected" {{ ($filters['status'] ?? '') === 'rejected' ? 'selected' : '' }}>Rejected</option>
                        </select>
                    </div>
                    <x-primary-button>{{ __('Filter') }}</x-primary-button>
                </form>
            </div>

            <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                <h3 class="text-lg font-medium text-gray-900">File Planned Time Adjustment</h3>

                <form method="POST" action="{{ route('time-adjustment-requests.store') }}" class="mt-4 space-y-4" enctype="multipart/form-data">
                    @csrf
                    <div>
                        <x-input-label for="date" :value="__('Date')" />
                        <x-text-input id="date" name="date" type="date" class="mt-1 block w-full" :value="old('date')" required />
                        <x-input-error class="mt-2" :messages="$errors->get('date')" />
                    </div>

                    <div>
                        <x-input-label for="adjustment_type" :value="__('Type')" />
                        <select id="adjustment_type" name="adjustment_type" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm w-full">
                            <option value="planned_late" {{ old('adjustment_type') === 'planned_late' ? 'selected' : '' }}>Late (planned)</option>
                            <option value="planned_early_out" {{ old('adjustment_type') === 'planned_early_out' ? 'selected' : '' }}>Early Out (planned)</option>
                            <option value="half_day" {{ old('adjustment_type') === 'half_day' ? 'selected' : '' }}>Half Day</option>
                            <option value="official_business" {{ old('adjustment_type') === 'official_business' ? 'selected' : '' }}>Official Business</option>
                            <option value="emergency_short_hours" {{ old('adjustment_type') === 'emergency_short_hours' ? 'selected' : '' }}>Emergency Leave (short hours)</option>
                        </select>
                        <x-input-error class="mt-2" :messages="$errors->get('adjustment_type')" />
                    </div>

                    <div>
                        <x-input-label for="minutes" :value="__('Minutes (Optional)')" />
                        <x-text-input id="minutes" name="minutes" type="number" min="0" step="1" class="mt-1 block w-full" :value="old('minutes')" />
                        <x-input-error class="mt-2" :messages="$errors->get('minutes')" />
                    </div>

                    <div>
                        <x-input-label for="reason" :value="__('Reason')" />
                        <x-text-input id="reason" name="reason" type="text" class="mt-1 block w-full" :value="old('reason')" />
                        <x-input-error class="mt-2" :messages="$errors->get('reason')" />
                    </div>

                    <div>
                        <x-input-label for="attachment" :value="__('Attachment (Optional)')" />
                        <input id="attachment" name="attachment" type="file" accept="image/*,.pdf,.doc,.docx,.xls,.xlsx" class="mt-1 block w-full" />
                        <x-input-error class="mt-2" :messages="$errors->get('attachment')" />
                    </div>

                    <x-primary-button>{{ __('Submit') }}</x-primary-button>
                </form>
            </div>

            <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                <div class="overflow-auto">
                    <table class="min-w-full text-sm">
                        <thead>
                            <tr class="border-b">
                                <th class="text-left py-2">Employee</th>
                                <th class="text-left py-2">Date</th>
                                <th class="text-left py-2">Type</th>
                                <th class="text-left py-2">Minutes</th>
                                <th class="text-left py-2">Reason</th>
                                <th class="text-left py-2">Attachment</th>
                                <th class="text-left py-2">Status</th>
                                <th class="text-left py-2">Approved By</th>
                                <th class="text-left py-2">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($requests as $r)
                                <tr class="border-b">
                                    <td class="py-2">{{ $r->employee?->full_name }}</td>
                                    <td class="py-2">{{ optional($r->date)->format('Y-m-d') }}</td>
                                    <td class="py-2">{{ $r->adjustment_type }}</td>
                                    <td class="py-2">{{ $r->minutes }}</td>
                                    <td class="py-2">{{ $r->reason }}</td>
                                    <td class="py-2">
                                        @if ($r->attachment_path)
                                            <a class="text-indigo-700 underline" href="{{ asset('storage/' . $r->attachment_path) }}" target="_blank">View</a>
                                        @else
                                            -
                                        @endif
                                    </td>
                                    <td class="py-2">{{ $r->status }}</td>
                                    <td class="py-2">{{ $r->approver?->full_name }}</td>
                                    <td class="py-2">
                                        @if (auth()->user()?->canManageBackoffice() && $r->status === 'pending')
                                            <form method="POST" action="{{ route('time-adjustment-requests.approve', $r->id) }}" class="inline">
                                                @csrf
                                                @method('patch')
                                                <x-primary-button>{{ __('Approve') }}</x-primary-button>
                                            </form>
                                            <form method="POST" action="{{ route('time-adjustment-requests.reject', $r->id) }}" class="inline">
                                                @csrf
                                                @method('patch')
                                                <x-danger-button>{{ __('Reject') }}</x-danger-button>
                                            </form>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="mt-4">{{ $requests->links() }}</div>
            </div>
        </div>
    </div>
</x-app-layout>
