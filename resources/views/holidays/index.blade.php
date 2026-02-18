<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('Holidays') }}</h2>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            @if (session('status') === 'holiday-created')
                <div class="p-3 rounded bg-green-50 text-green-700 border border-green-200">Holiday created.</div>
            @endif
            @if (session('status') === 'holiday-updated')
                <div class="p-3 rounded bg-green-50 text-green-700 border border-green-200">Holiday updated.</div>
            @endif
            @if (session('status') === 'holiday-deleted')
                <div class="p-3 rounded bg-green-50 text-green-700 border border-green-200">Holiday deleted.</div>
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
                    <form method="GET" class="flex flex-col sm:flex-row gap-3 sm:items-end">
                        <div>
                            <x-input-label for="year" :value="__('Year')" />
                            <x-text-input id="year" name="year" type="number" min="2020" max="2099" class="mt-1 block" value="{{ $year }}" />
                        </div>
                        <div>
                            <x-primary-button>Filter</x-primary-button>
                        </div>
                    </form>

                    <div class="border-t pt-4">
                        <div class="text-lg font-semibold">Add Holiday</div>
                        <form method="POST" action="{{ route('holidays.store') }}" class="mt-3 grid grid-cols-1 md:grid-cols-6 gap-3 items-end">
                            @csrf
                            <div class="md:col-span-2">
                                <x-input-label for="name" :value="__('Name')" />
                                <x-text-input id="name" name="name" type="text" class="mt-1 block w-full" required />
                            </div>
                            <div>
                                <x-input-label for="date" :value="__('Date')" />
                                <x-text-input id="date" name="date" type="date" class="mt-1 block w-full" required />
                            </div>
                            <div>
                                <x-input-label for="type" :value="__('Type')" />
                                <select id="type" name="type" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                                    <option value="regular">Regular</option>
                                    <option value="special_non_working">Special (Non-working)</option>
                                    <option value="special_working">Special (Working)</option>
                                </select>
                            </div>
                            <div>
                                <x-input-label for="pay_multiplier" :value="__('Pay Multiplier')" />
                                <x-text-input id="pay_multiplier" name="pay_multiplier" type="number" min="0.5" max="5" step="0.01" class="mt-1 block w-full" value="1" required />
                            </div>
                            <div class="md:col-span-6">
                                <x-input-label for="description" :value="__('Description (Optional)')" />
                                <x-text-input id="description" name="description" type="text" class="mt-1 block w-full" />
                            </div>
                            <div class="md:col-span-6">
                                <label class="inline-flex items-center gap-2">
                                    <input type="checkbox" name="recurring" value="1" class="rounded border-gray-300">
                                    <span class="text-sm text-gray-700">Recurring yearly</span>
                                </label>
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
                                    <th class="text-left py-2">Name</th>
                                    <th class="text-left py-2">Type</th>
                                    <th class="text-left py-2">Multiplier</th>
                                    <th class="text-left py-2">Recurring</th>
                                    <th class="text-left py-2">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($holidays as $h)
                                    <tr class="border-b align-top">
                                        <td class="py-2">{{ optional($h->date)->format('Y-m-d') }}</td>
                                        <td class="py-2 font-semibold">{{ $h->name }}</td>
                                        <td class="py-2">{{ $h->type }}</td>
                                        <td class="py-2">{{ number_format((float) $h->pay_multiplier, 2) }}</td>
                                        <td class="py-2">{{ $h->recurring ? 'Yes' : 'No' }}</td>
                                        <td class="py-2">
                                            <div class="flex gap-2">
                                                <x-secondary-button type="button" x-data x-on:click="$dispatch('open-modal', 'edit-holiday-{{ $h->id }}')">Edit</x-secondary-button>
                                                <form method="POST" action="{{ route('holidays.destroy', $h) }}" onsubmit="return confirm('Delete this holiday?');">
                                                    @csrf
                                                    @method('delete')
                                                    <x-danger-button>Delete</x-danger-button>
                                                </form>
                                            </div>

                                            <x-modal name="edit-holiday-{{ $h->id }}" :show="false" focusable>
                                                <form method="POST" action="{{ route('holidays.update', $h) }}" class="p-6 space-y-4">
                                                    @csrf
                                                    @method('patch')

                                                    <div class="text-lg font-medium text-gray-900">Edit Holiday</div>

                                                    <div>
                                                        <x-input-label :value="__('Name')" />
                                                        <x-text-input name="name" type="text" class="mt-1 block w-full" value="{{ old('name', $h->name) }}" required />
                                                    </div>
                                                    <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                                                        <div>
                                                            <x-input-label :value="__('Date')" />
                                                            <x-text-input name="date" type="date" class="mt-1 block w-full" value="{{ old('date', optional($h->date)->format('Y-m-d')) }}" required />
                                                        </div>
                                                        <div>
                                                            <x-input-label :value="__('Type')" />
                                                            <select name="type" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                                                                @php $t = old('type', $h->type); @endphp
                                                                <option value="regular" {{ $t === 'regular' ? 'selected' : '' }}>Regular</option>
                                                                <option value="special_non_working" {{ $t === 'special_non_working' ? 'selected' : '' }}>Special (Non-working)</option>
                                                                <option value="special_working" {{ $t === 'special_working' ? 'selected' : '' }}>Special (Working)</option>
                                                            </select>
                                                        </div>
                                                        <div>
                                                            <x-input-label :value="__('Pay Multiplier')" />
                                                            <x-text-input name="pay_multiplier" type="number" min="0.5" max="5" step="0.01" class="mt-1 block w-full" value="{{ old('pay_multiplier', $h->pay_multiplier) }}" required />
                                                        </div>
                                                    </div>
                                                    <div>
                                                        <x-input-label :value="__('Description (Optional)')" />
                                                        <x-text-input name="description" type="text" class="mt-1 block w-full" value="{{ old('description', $h->description) }}" />
                                                    </div>
                                                    <div>
                                                        <label class="inline-flex items-center gap-2">
                                                            <input type="checkbox" name="recurring" value="1" class="rounded border-gray-300" {{ old('recurring', $h->recurring) ? 'checked' : '' }}>
                                                            <span class="text-sm text-gray-700">Recurring yearly</span>
                                                        </label>
                                                    </div>

                                                    <div class="flex justify-end gap-2">
                                                        <x-secondary-button type="button" x-on:click="$dispatch('close-modal', 'edit-holiday-{{ $h->id }}')">Cancel</x-secondary-button>
                                                        <x-primary-button>Save</x-primary-button>
                                                    </div>
                                                </form>
                                            </x-modal>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="py-3 text-gray-600">No holidays for this year.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
