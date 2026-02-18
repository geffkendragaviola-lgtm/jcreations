<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Absence Notices') }}
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                <form method="GET" action="{{ route('absence-notices.index') }}" class="flex gap-2 items-end">
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
                <h3 class="text-lg font-medium text-gray-900">{{ __('File Unfiled Absence Justification') }}</h3>

                <form method="POST" action="{{ route('absence-notices.store') }}" class="mt-4 space-y-4" enctype="multipart/form-data">
                    @csrf
                    <div>
                        <x-input-label for="date" :value="__('Date')" />
                        <x-text-input id="date" name="date" type="date" class="mt-1 block w-full" :value="old('date')" required />
                        <x-input-error class="mt-2" :messages="$errors->get('date')" />
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
                                <th class="text-left py-2">Reason</th>
                                <th class="text-left py-2">Attachment</th>
                                <th class="text-left py-2">Status</th>
                                <th class="text-left py-2">Approved By</th>
                                <th class="text-left py-2">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($notices as $n)
                                <tr class="border-b">
                                    <td class="py-2">{{ $n->employee?->full_name }}</td>
                                    <td class="py-2">{{ optional($n->date)->format('Y-m-d') }}</td>
                                    <td class="py-2">{{ $n->reason }}</td>
                                    <td class="py-2">
                                        @if ($n->attachment_path)
                                            <a class="text-indigo-700 underline" href="{{ asset('storage/' . $n->attachment_path) }}" target="_blank">View</a>
                                        @else
                                            -
                                        @endif
                                    </td>
                                    <td class="py-2">{{ $n->status }}</td>
                                    <td class="py-2">{{ $n->approver?->full_name }}</td>
                                    <td class="py-2">
                                        @if (auth()->user()?->canManageBackoffice() && $n->status === 'pending')
                                            <form method="POST" action="{{ route('absence-notices.approve', $n->id) }}" class="inline">
                                                @csrf
                                                @method('patch')
                                                <x-primary-button>{{ __('Approve') }}</x-primary-button>
                                            </form>
                                            <form method="POST" action="{{ route('absence-notices.reject', $n->id) }}" class="inline">
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

                <div class="mt-4">{{ $notices->links() }}</div>
            </div>
        </div>
    </div>
</x-app-layout>
