<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('Schedule') }}</h2>
    </x-slot>

    <x-slot name="headerNav">
        <nav class="flex gap-6 -mb-px overflow-x-auto">
            <a href="{{ route('time-tracking.index') }}" class="inline-flex items-center px-1 py-3 border-b-2 text-sm font-medium {{ request()->routeIs('time-tracking.index') ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">Time Tracking</a>
            <a href="{{ route('time-tracking.logs') }}" class="inline-flex items-center px-1 py-3 border-b-2 text-sm font-medium {{ request()->routeIs('time-tracking.logs') ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">Logs</a>
            <a href="{{ route('work-schedules.index') }}" class="inline-flex items-center px-1 py-3 border-b-2 text-sm font-medium {{ request()->routeIs('work-schedules.*') ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">Schedule</a>
        </nav>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                    <div class="bg-white shadow-sm rounded-xl border border-gray-200 overflow-hidden">
                        <div class="px-6 py-5 border-b border-gray-200">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-lg bg-indigo-100 flex items-center justify-center shrink-0">
                                    <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                </div>
                                <div>
                                    <h3 class="text-lg font-bold text-gray-900">Schedule List</h3>
                                    <p class="text-xs text-gray-500 mt-0.5">View and manage employee work schedules</p>
                                </div>
                            </div>

                            <form method="GET" action="{{ route('work-schedules.index') }}" class="mt-4 grid grid-cols-1 md:grid-cols-12 gap-3 items-end">
                                <div class="md:col-span-5">
                                    <label class="text-xs font-medium text-gray-600 mb-1 block">Search</label>
                                    <div class="relative">
                                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                            <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                                        </div>
                                        <input name="search" type="text" value="{{ request('search') }}" placeholder="Search by code, first name, or last name" class="pl-9 border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-lg shadow-sm w-full text-sm" />
                                    </div>
                                </div>

                                <div class="md:col-span-4">
                                    <label class="text-xs font-medium text-gray-600 mb-1 block">Department</label>
                                    <select name="department_id" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-lg shadow-sm w-full text-sm">
                                        <option value="">All Departments</option>
                                        @foreach ($departments as $d)
                                            <option value="{{ $d->id }}" @selected((string) request('department_id') === (string) $d->id)>{{ $d->name }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="md:col-span-3 flex items-center gap-2">
                                    <button type="submit" class="flex-1 inline-flex items-center justify-center gap-1.5 px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-lg hover:bg-indigo-700 transition">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/></svg>
                                        Filter
                                    </button>
                                    <a href="{{ route('work-schedules.index') }}" class="flex-1 inline-flex items-center justify-center px-4 py-2 bg-white border border-gray-300 text-gray-700 text-sm font-medium rounded-lg hover:bg-gray-50 transition text-center">Reset</a>
                                </div>
                            </form>
                        </div>

                        <div class="px-6 py-5 text-gray-900">

                    @if (session('status') === 'schedule-updated')
                        <div class="mb-4 px-4 py-3 rounded-lg bg-emerald-50 text-emerald-700 border border-emerald-200 flex items-center gap-2 text-sm">
                            <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            Schedule updated successfully.
                        </div>
                    @endif

                    @if ($errors->any())
                        <div class="mb-4 px-4 py-3 rounded-lg bg-red-50 text-red-700 border border-red-200 text-sm">
                            <div class="font-semibold flex items-center gap-2">
                                <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                Please fix the errors and try again.
                            </div>
                            <ul class="list-disc ml-8 mt-1">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <div class="overflow-x-auto rounded-lg border border-gray-200">
                        <table class="min-w-full text-sm">
                            <thead>
                                <tr class="bg-gray-50 border-b border-gray-200">
                                    <th class="text-left py-3 px-4 text-xs font-semibold text-gray-600 uppercase tracking-wider">Code</th>
                                    <th class="text-left py-3 px-4 text-xs font-semibold text-gray-600 uppercase tracking-wider">Name</th>
                                    <th class="text-left py-3 px-4 text-xs font-semibold text-gray-600 uppercase tracking-wider">Department</th>
                                    <th class="text-left py-3 px-4 text-xs font-semibold text-gray-600 uppercase tracking-wider">Schedule</th>
                                    <th class="text-left py-3 px-4 text-xs font-semibold text-gray-600 uppercase tracking-wider">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                @foreach ($employees as $e)
                                    @php
                                        $scheduleMap = $e->schedules->keyBy('day_of_week');
                                        $defaultStart = $e->department?->business_hours_start;
                                        $defaultEnd = $e->department?->business_hours_end;
                                    @endphp

                                    <tr class="hover:bg-gray-50/50 transition align-top">
                                        <td class="py-3 px-4">
                                            <span class="inline-flex items-center px-2.5 py-1 rounded-md bg-indigo-50 text-indigo-700 font-mono font-semibold text-xs border border-indigo-200">{{ $e->employee_code }}</span>
                                        </td>
                                        <td class="py-3 px-4 font-medium text-gray-900">{{ $e->full_name }}</td>
                                        <td class="py-3 px-4 text-gray-600">{{ $e->department?->name ?? '-' }}</td>
                                        <td class="py-3 px-4">
                                            <div class="space-y-0.5">
                                                @foreach ($days as $day)
                                                    @php
                                                        $s = $scheduleMap->get($day);
                                                        $start = $s?->start_time ?? $defaultStart;
                                                        $end = $s?->end_time ?? $defaultEnd;
                                                    @endphp
                                                    <div class="flex items-center gap-2">
                                                        <div class="w-20 text-xs font-medium text-gray-500">{{ $day }}</div>
                                                        <div class="text-xs text-gray-700 font-mono">
                                                            {{ $start && $end ? ($start . ' - ' . $end) : '-' }}
                                                        </div>
                                                    </div>
                                                @endforeach
                                            </div>
                                        </td>
                                        <td class="py-3 px-4">
                                            <div class="flex items-center gap-1.5">
                                                <button type="button" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-indigo-50 text-indigo-600 text-xs font-medium hover:bg-indigo-100 transition" x-data="" x-on:click.prevent="$dispatch('open-modal', 'edit-schedule-{{ $e->id }}')">
                                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                                    Edit
                                                </button>
                                                <a href="{{ route('work-schedules.calendar', $e) }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-gray-100 text-gray-700 text-xs font-medium hover:bg-gray-200 transition">
                                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                                    Calendar
                                                </a>
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
