<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('Schedule') }}</h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="flex flex-col lg:flex-row gap-6">
                <aside class="w-full lg:w-64">
                    <div class="bg-white shadow-sm rounded-lg border border-gray-200 overflow-hidden">
                        <div class="px-4 py-4 border-b bg-gray-50">
                            <div class="text-sm font-semibold text-gray-900">Schedule</div>
                        </div>

                        <nav class="p-2">
                            @php
                                $navItems = [
                                    [
                                        'label' => 'Schedule List',
                                        'route' => 'work-schedules.index',
                                    ],
                                    [
                                        'label' => 'Calendar',
                                        'route' => null,
                                    ],
                                    [
                                        'label' => 'Add Schedule',
                                        'route' => null,
                                    ],
                                    [
                                        'label' => 'Set Employee Schedule Type',
                                        'route' => null,
                                    ],
                                ];
                            @endphp

                            @foreach ($navItems as $item)
                                @if (!empty($item['route']))
                                    <a href="{{ route($item['route']) }}" class="block px-3 py-2 rounded-md text-sm bg-indigo-50 text-indigo-700 font-semibold">
                                        {{ $item['label'] }}
                                    </a>
                                @else
                                    <div class="px-3 py-2 rounded-md text-sm text-gray-400 cursor-not-allowed">
                                        {{ $item['label'] }}
                                    </div>
                                @endif
                            @endforeach
                        </nav>
                    </div>
                </aside>

                <main class="flex-1">
                    <div class="bg-white shadow-sm rounded-lg border border-gray-200 overflow-hidden">
                        <div class="p-5 border-b">
                            <div>
                                <div class="text-sm text-gray-500">Schedule</div>
                                <div class="text-lg font-semibold text-gray-900">Schedule List</div>
                            </div>

                            <form method="GET" action="{{ route('work-schedules.index') }}" class="mt-4 grid grid-cols-1 md:grid-cols-12 gap-3 items-end">
                                <div class="md:col-span-6">
                                    <div class="text-xs text-gray-600 mb-1">Search</div>
                                    <input name="search" type="text" value="{{ request('search') }}" placeholder="Search by code / first name / last name" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm w-full" />
                                </div>

                                <div class="md:col-span-4">
                                    <div class="text-xs text-gray-600 mb-1">Filter by Department</div>
                                    <select name="department_id" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm w-full">
                                        <option value="">All Departments</option>
                                        @foreach ($departments as $d)
                                            <option value="{{ $d->id }}" @selected((string) request('department_id') === (string) $d->id)>{{ $d->name }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="md:col-span-2 flex items-center gap-2">
                                    <button type="submit" class="w-full px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700">Filter</button>
                                    <a href="{{ route('work-schedules.index') }}" class="w-full px-4 py-2 bg-gray-100 text-gray-800 rounded-md hover:bg-gray-200 text-center">Reset</a>
                                </div>
                            </form>
                        </div>

                        <div class="p-5 text-gray-900">

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

                    <div class="overflow-x-auto rounded-lg border border-gray-200">
                        <table class="min-w-full text-sm">
                            <thead class="bg-gray-50 text-gray-700">
                                <tr class="border-b">
                                    <th class="text-left py-3 px-3 font-semibold">Code</th>
                                    <th class="text-left py-3 px-3 font-semibold">Name</th>
                                    <th class="text-left py-3 px-3 font-semibold">Department</th>
                                    <th class="text-left py-3 px-3 font-semibold">Schedule</th>
                                    <th class="text-left py-3 px-3 font-semibold">Action</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y">
                                @foreach ($employees as $e)
                                    @php
                                        $scheduleMap = $e->schedules->keyBy('day_of_week');
                                        $defaultStart = $e->department?->business_hours_start;
                                        $defaultEnd = $e->department?->business_hours_end;
                                    @endphp

                                    <tr class="hover:bg-gray-50 align-top">
                                        <td class="py-3 px-3 font-medium">{{ $e->employee_code }}</td>
                                        <td class="py-3 px-3">{{ $e->full_name }}</td>
                                        <td class="py-3 px-3">{{ $e->department?->name ?? '-' }}</td>
                                        <td class="py-3 px-3">
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
                                        <td class="py-3 px-3">
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
                </main>
            </div>
        </div>
    </div>
</x-app-layout>
