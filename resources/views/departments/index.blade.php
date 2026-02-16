<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Departments') }}
            </h2>

            <x-primary-button x-data="" x-on:click.prevent="$dispatch('open-modal', 'create-department')">Create</x-primary-button>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">

                    @if (session('status') === 'department-created')
                        <div class="mb-4 p-3 rounded bg-green-50 text-green-700 border border-green-200">
                            Department created.
                        </div>
                    @endif

                    @if (session('status') === 'department-updated')
                        <div class="mb-4 p-3 rounded bg-green-50 text-green-700 border border-green-200">
                            Department updated.
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
                        <table class="min-w-full">
                            <thead>
                                <tr class="border-b">
                                    <th class="text-left py-3 px-2">Name</th>
                                    <th class="text-left py-3 px-2">Business Hours</th>
                                    <th class="text-left py-3 px-2">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($departments as $d)
                                    @php
                                        $start = is_string($d->business_hours_start) ? substr($d->business_hours_start, 0, 5) : null;
                                        $end = is_string($d->business_hours_end) ? substr($d->business_hours_end, 0, 5) : null;
                                    @endphp
                                    <tr class="border-b align-top">
                                        <td class="py-3 px-2 font-medium">{{ $d->name }}</td>
                                        <td class="py-3 px-2 text-sm text-gray-700">
                                            {{ $start && $end ? ($start . ' - ' . $end) : '-' }}
                                        </td>
                                        <td class="py-3 px-2">
                                            <x-secondary-button x-data="" x-on:click.prevent="$dispatch('open-modal', 'edit-department-{{ $d->id }}')">Edit</x-secondary-button>

                                            <x-modal name="edit-department-{{ $d->id }}" :show="false" focusable>
                                                <form method="POST" action="{{ route('departments.update', $d) }}" class="p-6 space-y-4">
                                                    @csrf
                                                    @method('PATCH')

                                                    <input type="hidden" name="redirect_to" value="{{ url()->full() }}" />

                                                    <div class="text-lg font-medium text-gray-900">Edit Department</div>

                                                    <div>
                                                        <div class="text-sm text-gray-700">Name</div>
                                                        <input type="text" name="name" value="{{ old('name', $d->name) }}" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm w-full" required />
                                                    </div>

                                                    <div>
                                                        <div class="text-sm text-gray-700">Description</div>
                                                        <textarea name="description" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm w-full" rows="3">{{ old('description', $d->description) }}</textarea>
                                                    </div>

                                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                                                        <div>
                                                            <div class="text-sm text-gray-700">Business Hours Start</div>
                                                            <input type="time" name="business_hours_start" value="{{ old('business_hours_start', $start ?? '') }}" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm w-full" />
                                                        </div>
                                                        <div>
                                                            <div class="text-sm text-gray-700">Business Hours End</div>
                                                            <input type="time" name="business_hours_end" value="{{ old('business_hours_end', $end ?? '') }}" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm w-full" />
                                                        </div>
                                                    </div>

                                                    <div class="mt-6 flex justify-end gap-2">
                                                        <x-secondary-button x-on:click="$dispatch('close-modal', 'edit-department-{{ $d->id }}')">Cancel</x-secondary-button>
                                                        <x-primary-button>Save</x-primary-button>
                                                    </div>
                                                </form>
                                            </x-modal>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <x-modal name="create-department" :show="false" focusable>
                        <form method="POST" action="{{ route('departments.store') }}" class="p-6 space-y-4">
                            @csrf

                            <div class="text-lg font-medium text-gray-900">Create Department</div>

                            <div>
                                <div class="text-sm text-gray-700">Name</div>
                                <input type="text" name="name" value="{{ old('name') }}" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm w-full" required />
                            </div>

                            <div>
                                <div class="text-sm text-gray-700">Description</div>
                                <textarea name="description" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm w-full" rows="3">{{ old('description') }}</textarea>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                                <div>
                                    <div class="text-sm text-gray-700">Business Hours Start</div>
                                    <input type="time" name="business_hours_start" value="{{ old('business_hours_start', '08:00') }}" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm w-full" />
                                </div>
                                <div>
                                    <div class="text-sm text-gray-700">Business Hours End</div>
                                    <input type="time" name="business_hours_end" value="{{ old('business_hours_end', '17:00') }}" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm w-full" />
                                </div>
                            </div>

                            <div class="mt-6 flex justify-end gap-2">
                                <x-secondary-button x-on:click="$dispatch('close-modal', 'create-department')">Cancel</x-secondary-button>
                                <x-primary-button>Create</x-primary-button>
                            </div>
                        </form>
                    </x-modal>

                </div>
            </div>
        </div>
    </div>
</x-app-layout>
