<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Absence Requests') }}
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                <form method="GET" action="{{ route('leave-requests.index') }}" class="flex gap-2 items-end">
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
                <h3 class="text-lg font-medium text-gray-900">{{ __('File Time Off') }}</h3>

                <form method="POST" action="{{ route('leave-requests.store') }}" class="mt-4 space-y-4" enctype="multipart/form-data">
                    @csrf
                    <div>
                        <x-input-label for="leave_type" :value="__('Type')" />
                        <x-text-input id="leave_type" name="leave_type" type="text" class="mt-1 block w-full" value="leave without pay" />
                        <x-input-error class="mt-2" :messages="$errors->get('leave_type')" />
                    </div>
                    <div>
                        <x-input-label for="start_date" :value="__('Start Date')" />
                        <x-text-input id="start_date" name="start_date" type="date" class="mt-1 block w-full" :value="old('start_date')" required />
                        <x-input-error class="mt-2" :messages="$errors->get('start_date')" />
                    </div>
                    <div>
                        <x-input-label for="end_date" :value="__('End Date')" />
                        <x-text-input id="end_date" name="end_date" type="date" class="mt-1 block w-full" :value="old('end_date')" required />
                        <x-input-error class="mt-2" :messages="$errors->get('end_date')" />
                    </div>
                    <div>
                        <x-input-label for="day_type" :value="__('Duration Type')" />
                        <select id="day_type" name="day_type" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm w-full">
                            <option value="full_day" {{ old('day_type') === 'full_day' ? 'selected' : '' }}>Full Day</option>
                            <option value="half_day" {{ old('day_type') === 'half_day' ? 'selected' : '' }}>Half Day</option>
                        </select>
                        <x-input-error class="mt-2" :messages="$errors->get('day_type')" />
                    </div>
                    <div>
                        <x-input-label for="description" :value="__('Description / Reason')" />
                        <x-text-input id="description" name="description" type="text" class="mt-1 block w-full" :value="old('description')" />
                        <x-input-error class="mt-2" :messages="$errors->get('description')" />
                    </div>
                    <div>
                        <x-input-label for="image" :value="__('Image (Optional)')" />
                        <input id="image" name="image" type="file" accept="image/*" class="mt-1 block w-full" />
                        <x-input-error class="mt-2" :messages="$errors->get('image')" />
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
                                <th class="text-left py-2">Dates</th>
                                <th class="text-left py-2">Type</th>
                                <th class="text-left py-2">Duration</th>
                                <th class="text-left py-2">Description</th>
                                <th class="text-left py-2">Image</th>
                                <th class="text-left py-2">Status</th>
                                <th class="text-left py-2">Approved By</th>
                                <th class="text-left py-2">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($requests as $r)
                                <tr class="border-b">
                                    <td class="py-2">{{ $r->employee?->full_name }}</td>
                                    <td class="py-2">{{ optional($r->start_date)->format('Y-m-d') }} to {{ optional($r->end_date)->format('Y-m-d') }}</td>
                                    <td class="py-2">{{ $r->leave_type }}</td>
                                    <td class="py-2">{{ $r->duration_days }}</td>
                                    <td class="py-2">{{ $r->description }}</td>
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
                                            <form method="POST" action="{{ route('leave-requests.approve', $r->id) }}" class="inline">
                                                @csrf
                                                @method('patch')
                                                <x-primary-button>{{ __('Approve') }}</x-primary-button>
                                            </form>
                                            <form method="POST" action="{{ route('leave-requests.reject', $r->id) }}" class="inline">
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
