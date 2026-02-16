<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between gap-3">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Work Schedules') }}
            </h2>

            <form method="GET" action="{{ route('work-schedules.index') }}" class="w-full max-w-3xl flex flex-col md:flex-row md:items-center gap-2">
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
                    <a href="{{ route('work-schedules.index') }}" class="px-4 py-2 bg-gray-100 text-gray-800 rounded hover:bg-gray-200">Reset</a>
                </div>
            </form>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">

                    @if (session('status') === 'schedule-updated')
                        <div class="mb-4 p-3 rounded bg-green-50 text-green-700 border border-green-200">
                            Schedule updated.
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
                                    <th class="text-left py-3 px-2">Schedule</th>
                                    <th class="text-left py-3 px-2">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($employees as $e)
                                    @php
                                        $scheduleMap = $e->schedules->keyBy('day_of_week');
                                        $defaultStart = $e->department?->business_hours_start;
                                        $defaultEnd = $e->department?->business_hours_end;
                                    @endphp

                                    <tr class="border-b align-top">
                                        <td class="py-3 px-2 font-medium">{{ $e->employee_code }}</td>
                                        <td class="py-3 px-2">{{ $e->full_name }}</td>
                                        <td class="py-3 px-2">{{ $e->department?->name ?? '-' }}</td>
                                        <td class="py-3 px-2">
                                            <div class="space-y-1">
                                                @foreach ($days as $day)
                                                    @php
                                                        $s = $scheduleMap->get($day);
                                                        $start = $s?->start_time ?? $defaultStart;
                                                        $end = $s?->end_time ?? $defaultEnd;
                                                    @endphp
                                                    <div class="flex items-center gap-2">
                                                        <div class="w-24 text-xs text-gray-600">{{ $day }}</div>
                                                        <div class="text-xs">
                                                            {{ $start && $end ? ($start . ' - ' . $end) : '-' }}
                                                        </div>
                                                    </div>
                                                @endforeach
                                            </div>
                                        </td>
                                        <td class="py-3 px-2">
                                            <div class="flex items-center gap-2">
                                                <x-secondary-button x-data="" x-on:click.prevent="$dispatch('open-modal', 'edit-schedule-{{ $e->id }}')">Edit</x-secondary-button>
                                                <a href="{{ route('work-schedules.calendar', $e) }}" class="px-4 py-2 bg-gray-100 text-gray-800 rounded hover:bg-gray-200">Calendar</a>
                                            </div>

                                            <x-modal name="edit-schedule-{{ $e->id }}" :show="false" focusable>
                                                <form method="POST" action="{{ route('work-schedules.update', $e) }}" class="p-6 space-y-4">
                                                    @csrf
                                                    @method('PATCH')

                                                    <input type="hidden" name="redirect_to" value="{{ url()->full() }}" />

                                                    <div class="text-lg font-medium text-gray-900">Edit Schedule</div>
                                                    <div class="text-sm text-gray-600">{{ $e->employee_code }} - {{ $e->full_name }}</div>

                                                    <div class="border-t pt-4">
                                                        <div class="text-sm font-medium text-gray-900">Weekly Schedule</div>
                                                        <div class="text-xs text-gray-600">
                                                            Leave a day blank to use the department default ({{ $defaultStart ?? '-' }} - {{ $defaultEnd ?? '-' }}).
                                                        </div>
                                                    </div>

                                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                                                        @foreach ($days as $day)
                                                            @php
                                                                $s = $scheduleMap->get($day);
                                                                $startVal = old('schedule.' . $day . '.start', $s?->start_time);
                                                                $endVal = old('schedule.' . $day . '.end', $s?->end_time);
                                                            @endphp
                                                            <div class="border rounded p-3">
                                                                <div class="text-sm font-medium text-gray-900">{{ $day }}</div>
                                                                <div class="grid grid-cols-2 gap-2 mt-2">
                                                                    <div>
                                                                        <div class="text-xs text-gray-600 mb-1">Start</div>
                                                                        <input type="time" name="schedule[{{ $day }}][start]" value="{{ $startVal }}" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm w-full" />
                                                                    </div>
                                                                    <div>
                                                                        <div class="text-xs text-gray-600 mb-1">End</div>
                                                                        <input type="time" name="schedule[{{ $day }}][end]" value="{{ $endVal }}" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm w-full" />
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        @endforeach
                                                    </div>

                                                    <div class="mt-6 flex justify-end gap-2">
                                                        <x-secondary-button x-on:click="$dispatch('close-modal', 'edit-schedule-{{ $e->id }}')">Cancel</x-secondary-button>
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

                    <div class="mt-4">
                        {{ $employees->links() }}
                    </div>

                </div>
            </div>
        </div>
    </div>
</x-app-layout>
