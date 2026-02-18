<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('Disciplinary Actions') }}</h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            @if (session('status') === 'action-created')
                <div class="p-3 rounded bg-green-50 text-green-700 border border-green-200">Action created.</div>
            @endif
            @if (session('status') === 'action-updated')
                <div class="p-3 rounded bg-green-50 text-green-700 border border-green-200">Action updated.</div>
            @endif
            @if (session('status') === 'action-deleted')
                <div class="p-3 rounded bg-green-50 text-green-700 border border-green-200">Action deleted.</div>
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
                <div class="p-6 text-gray-900 space-y-4">
                    <form method="GET" class="grid grid-cols-1 md:grid-cols-6 gap-3 items-end">
                        <div class="md:col-span-3">
                            <x-input-label for="search" :value="__('Search')" />
                            <x-text-input id="search" name="search" type="text" class="mt-1 block w-full" value="{{ $filters['search'] ?? '' }}" placeholder="Employee code or name" />
                        </div>
                        <div>
                            <x-input-label for="status" :value="__('Status')" />
                            @php $st = $filters['status'] ?? ''; @endphp
                            <select id="status" name="status" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                                <option value="" {{ $st === '' ? 'selected' : '' }}>All</option>
                                <option value="open" {{ $st === 'open' ? 'selected' : '' }}>Open</option>
                                <option value="resolved" {{ $st === 'resolved' ? 'selected' : '' }}>Resolved</option>
                                <option value="escalated" {{ $st === 'escalated' ? 'selected' : '' }}>Escalated</option>
                            </select>
                        </div>
                        <div>
                            <x-primary-button>Filter</x-primary-button>
                        </div>
                    </form>

                    <div class="border-t pt-4">
                        <div class="text-lg font-semibold">Add Action</div>
                        <form method="POST" action="{{ route('disciplinary-actions.store') }}" enctype="multipart/form-data" class="mt-3 grid grid-cols-1 md:grid-cols-6 gap-3 items-end">
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
                                <x-input-label for="type" :value="__('Type')" />
                                <x-text-input id="type" name="type" type="text" class="mt-1 block w-full" required />
                            </div>
                            <div>
                                <x-input-label for="severity" :value="__('Severity')" />
                                <select id="severity" name="severity" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" required>
                                    <option value="minor">Minor</option>
                                    <option value="moderate">Moderate</option>
                                    <option value="major">Major</option>
                                    <option value="critical">Critical</option>
                                </select>
                            </div>
                            <div>
                                <x-input-label for="incident_date" :value="__('Incident Date')" />
                                <x-text-input id="incident_date" name="incident_date" type="date" class="mt-1 block w-full" required />
                            </div>
                            <div>
                                <x-input-label for="attachment" :value="__('Attachment (Optional)')" />
                                <input id="attachment" name="attachment" type="file" class="mt-1 block w-full text-sm" />
                            </div>
                            <div class="md:col-span-6">
                                <x-input-label for="description" :value="__('Description')" />
                                <textarea id="description" name="description" rows="3" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" required></textarea>
                            </div>
                            <div class="md:col-span-6">
                                <x-input-label for="action_taken" :value="__('Action Taken (Optional)')" />
                                <x-text-input id="action_taken" name="action_taken" type="text" class="mt-1 block w-full" />
                            </div>
                            <div class="md:col-span-6 flex justify-end">
                                <x-primary-button>Add</x-primary-button>
                            </div>
                        </form>
                    </div>

                    <div class="border-t pt-4 overflow-x-auto">
                        <table class="min-w-full text-sm">
                            <thead>
                                <tr class="border-b">
                                    <th class="text-left py-2">Date</th>
                                    <th class="text-left py-2">Employee</th>
                                    <th class="text-left py-2">Type</th>
                                    <th class="text-left py-2">Severity</th>
                                    <th class="text-left py-2">Status</th>
                                    <th class="text-left py-2">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($actions as $a)
                                    <tr class="border-b align-top">
                                        <td class="py-2">{{ optional($a->incident_date)->format('Y-m-d') }}</td>
                                        <td class="py-2">{{ $a->employee?->full_name }}</td>
                                        <td class="py-2">{{ $a->type }}</td>
                                        <td class="py-2">{{ $a->severity }}</td>
                                        <td class="py-2">{{ $a->status }}</td>
                                        <td class="py-2">
                                            <div class="flex gap-2">
                                                <x-secondary-button type="button" x-data x-on:click="$dispatch('open-modal', 'edit-action-{{ $a->id }}')">Edit</x-secondary-button>
                                                <form method="POST" action="{{ route('disciplinary-actions.destroy', $a) }}" onsubmit="return confirm('Delete this record?');">
                                                    @csrf
                                                    @method('delete')
                                                    <x-danger-button>Delete</x-danger-button>
                                                </form>
                                            </div>

                                            <x-modal name="edit-action-{{ $a->id }}" :show="false" focusable>
                                                <form method="POST" action="{{ route('disciplinary-actions.update', $a) }}" class="p-6 space-y-4">
                                                    @csrf
                                                    @method('patch')

                                                    <div class="text-lg font-medium text-gray-900">Edit Action</div>
                                                    <div class="text-sm text-gray-600">{{ $a->employee?->employee_code }} - {{ $a->employee?->full_name }}</div>

                                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                                                        <div>
                                                            <x-input-label :value="__('Type')" />
                                                            <x-text-input name="type" type="text" class="mt-1 block w-full" value="{{ old('type', $a->type) }}" required />
                                                        </div>
                                                        <div>
                                                            <x-input-label :value="__('Severity')" />
                                                            @php $sev = old('severity', $a->severity); @endphp
                                                            <select name="severity" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" required>
                                                                <option value="minor" {{ $sev === 'minor' ? 'selected' : '' }}>Minor</option>
                                                                <option value="moderate" {{ $sev === 'moderate' ? 'selected' : '' }}>Moderate</option>
                                                                <option value="major" {{ $sev === 'major' ? 'selected' : '' }}>Major</option>
                                                                <option value="critical" {{ $sev === 'critical' ? 'selected' : '' }}>Critical</option>
                                                            </select>
                                                        </div>
                                                        <div>
                                                            <x-input-label :value="__('Incident Date')" />
                                                            <x-text-input name="incident_date" type="date" class="mt-1 block w-full" value="{{ old('incident_date', optional($a->incident_date)->format('Y-m-d')) }}" required />
                                                        </div>
                                                        <div>
                                                            <x-input-label :value="__('Status')" />
                                                            @php $s = old('status', $a->status); @endphp
                                                            <select name="status" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" required>
                                                                <option value="open" {{ $s === 'open' ? 'selected' : '' }}>Open</option>
                                                                <option value="resolved" {{ $s === 'resolved' ? 'selected' : '' }}>Resolved</option>
                                                                <option value="escalated" {{ $s === 'escalated' ? 'selected' : '' }}>Escalated</option>
                                                            </select>
                                                        </div>
                                                    </div>

                                                    <div>
                                                        <x-input-label :value="__('Description')" />
                                                        <textarea name="description" rows="3" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" required>{{ old('description', $a->description) }}</textarea>
                                                    </div>

                                                    <div>
                                                        <x-input-label :value="__('Action Taken (Optional)')" />
                                                        <x-text-input name="action_taken" type="text" class="mt-1 block w-full" value="{{ old('action_taken', $a->action_taken) }}" />
                                                    </div>

                                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                                                        <div>
                                                            <x-input-label :value="__('Resolution Date (Optional)')" />
                                                            <x-text-input name="resolution_date" type="date" class="mt-1 block w-full" value="{{ old('resolution_date', optional($a->resolution_date)->format('Y-m-d')) }}" />
                                                        </div>
                                                        <div>
                                                            <x-input-label :value="__('Resolution Notes (Optional)')" />
                                                            <x-text-input name="resolution_notes" type="text" class="mt-1 block w-full" value="{{ old('resolution_notes', $a->resolution_notes) }}" />
                                                        </div>
                                                    </div>

                                                    <div class="flex justify-end gap-2">
                                                        <x-secondary-button type="button" x-on:click="$dispatch('close-modal', 'edit-action-{{ $a->id }}')">Cancel</x-secondary-button>
                                                        <x-primary-button>Save</x-primary-button>
                                                    </div>
                                                </form>
                                            </x-modal>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="py-3 text-gray-600">No records found.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    @if (method_exists($actions, 'links'))
                        <div class="pt-4">{{ $actions->links() }}</div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
