<x-app-layout>
    <x-slot name="header">
        @php
            $prev = $month->copy()->subMonth()->format('Y-m');
            $next = $month->copy()->addMonth()->format('Y-m');
            $todayKey = now()->format('Y-m-d');
            $deptStart = $employee->department?->business_hours_start ?? '08:00';
            $deptEnd = $employee->department?->business_hours_end ?? '17:00';
        @endphp

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
                    <div class="bg-white shadow-sm rounded-lg border border-gray-200 overflow-hidden">
                        <div class="p-5 border-b">
                            <div class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
                                <div>
                                    <div class="text-sm text-gray-500">Schedule</div>
                                    <div class="text-lg font-semibold text-gray-900">Schedule Calendar</div>
                                    <div class="text-sm text-gray-600">
                                        {{ $employee->employee_code }} - {{ $employee->full_name }}
                                        @if ($employee->department)
                                            <span class="text-gray-400">|</span> {{ $employee->department->name }}
                                        @endif
                                    </div>
                                </div>

                                <div class="flex flex-wrap items-center gap-2">
                                    <a href="{{ route('work-schedules.index') }}" class="px-4 py-2 bg-gray-100 text-gray-800 rounded hover:bg-gray-200">Back</a>
                                    <div class="w-px h-6 bg-gray-200"></div>
                                    <a href="{{ route('work-schedules.calendar', [$employee, 'month' => $prev]) }}" class="px-3 py-2 bg-white border border-gray-200 text-gray-800 rounded hover:bg-gray-50">&larr;</a>
                                    <div class="px-3 py-2 text-sm font-medium text-gray-800">
                                        {{ $month->format('F Y') }}
                                    </div>
                                    <a href="{{ route('work-schedules.calendar', [$employee, 'month' => $next]) }}" class="px-3 py-2 bg-white border border-gray-200 text-gray-800 rounded hover:bg-gray-50">&rarr;</a>
                                </div>
                            </div>
                        </div>

                        <div class="p-5 text-gray-900">

                    @if (session('status') === 'schedule-override-updated')
                        <div class="mb-4 p-3 rounded bg-green-50 text-green-700 border border-green-200">
                            Schedule override updated.
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

                    <div class="border border-gray-200 rounded-xl overflow-hidden">
                        <div class="grid grid-cols-7 bg-gray-50 border-b border-gray-200">
                            @foreach (['Sun','Mon','Tue','Wed','Thu','Fri','Sat'] as $h)
                                <div class="text-[11px] font-semibold text-gray-600 px-3 py-2 tracking-wide">{{ $h }}</div>
                            @endforeach
                        </div>

                        <div class="grid grid-cols-7">
                            @foreach ($days as $d)
                                @php
                                    $isToday = $d['key'] === $todayKey;
                                    $cellClasses = 'min-h-[128px] border-b border-r border-gray-200 p-2 md:p-3 flex flex-col gap-2';
                                    $cellClasses .= $d['in_month'] ? ' bg-white' : ' bg-gray-50';
                                    if ($isToday) {
                                        $cellClasses .= ' bg-indigo-50';
                                    }

                                    $dayNumberClasses = 'text-xs font-medium';
                                    $dayNumberClasses .= $d['in_month'] ? ' text-gray-800' : ' text-gray-400';
                                    if ($isToday) {
                                        $dayNumberClasses .= ' text-indigo-700';
                                    }

                                    $timeText = $d['working'] && $d['start'] && $d['end'] ? ($d['start'] . ' - ' . $d['end']) : 'Off';
                                @endphp

                                <button
                                    type="button"
                                    class="{{ $cellClasses }} text-left hover:bg-indigo-50"
                                    x-data=""
                                    x-on:click.prevent="$dispatch('open-modal', 'edit-override-{{ $d['key'] }}')"
                                >
                                    <div class="flex items-start justify-between">
                                        <div class="{{ $dayNumberClasses }}">
                                            {{ $d['date']->day }}
                                        </div>
                                        @if ($d['has_override'])
                                            <div class="text-[10px] px-2 py-0.5 rounded-full bg-indigo-100 text-indigo-700">
                                                Override
                                            </div>
                                        @endif
                                    </div>

                                    <div class="flex-1">
                                        @if (!empty($d['labels']))
                                            <div class="flex flex-wrap gap-1 mb-1">
                                                @foreach ($d['labels'] as $lbl)
                                                    @php
                                                        $lbl = (string) $lbl;
                                                        $lblClass = 'bg-gray-100 text-gray-700';
                                                        if ($lbl === 'Overtime') {
                                                            $lblClass = 'bg-amber-100 text-amber-900';
                                                        } elseif ($lbl === 'On Leave') {
                                                            $lblClass = 'bg-emerald-100 text-emerald-800';
                                                        } elseif ($lbl === 'On Absence') {
                                                            $lblClass = 'bg-red-100 text-red-800';
                                                        }
                                                    @endphp
                                                    <div class="text-[10px] font-semibold px-2 py-0.5 rounded-full {{ $lblClass }}">
                                                        {{ $lbl }}
                                                    </div>
                                                @endforeach
                                            </div>
                                        @endif

                                        @if ($d['working'] && $d['start'] && $d['end'])
                                            <div class="inline-flex items-center max-w-full">
                                                <div class="truncate text-xs font-semibold text-indigo-800 bg-indigo-100 px-2 py-1 rounded-md">
                                                    {{ $timeText }}
                                                </div>
                                            </div>
                                        @else
                                            <div class="text-xs text-gray-400">{{ $timeText }}</div>
                                        @endif

                                        @if (!empty($d['ot']))
                                            <div class="mt-1 space-y-1">
                                                @php
                                                    $otItems = $d['ot'];
                                                    $otShown = array_slice($otItems, 0, 2);
                                                    $otMore = count($otItems) - count($otShown);
                                                @endphp

                                                @foreach ($otShown as $ot)
                                                    <div class="inline-flex items-center max-w-full">
                                                        <div class="truncate text-xs font-semibold text-amber-900 bg-amber-100 px-2 py-1 rounded-md">
                                                            <span title="{{ trim(($ot['label'] ?? 'OT') . ((isset($ot['reason']) && $ot['reason']) ? (' - ' . $ot['reason']) : '')) }}">
                                                                {{ $ot['label'] ?? 'OT' }}
                                                            </span>
                                                        </div>
                                                    </div>
                                                @endforeach

                                                @if ($otMore > 0)
                                                    <div class="inline-flex items-center max-w-full">
                                                        <div class="truncate text-xs font-semibold text-amber-900 bg-amber-50 px-2 py-1 rounded-md border border-amber-200">
                                                            +{{ $otMore }} more
                                                        </div>
                                                    </div>
                                                @endif
                                            </div>
                                        @endif
                                    </div>

                                    <div class="text-[10px] text-gray-400">{{ $d['dow'] }}</div>
                                </button>

                                <x-modal name="edit-override-{{ $d['key'] }}" :show="false" focusable>
                                    <form
                                        method="POST"
                                        action="{{ route('work-schedules.override', $employee) }}"
                                        class="p-6 space-y-4"
                                        x-data="{
                                            isWorking: {{ $d['working'] ? 'true' : 'false' }},
                                            startTime: '{{ $d['start'] ?? '' }}',
                                            endTime: '{{ $d['end'] ?? '' }}',
                                            baselineStart: '{{ $d['baseline_start'] ?? '' }}',
                                            baselineEnd: '{{ $d['baseline_end'] ?? '' }}',
                                            deptStart: '{{ $deptStart ?? '' }}',
                                            deptEnd: '{{ $deptEnd ?? '' }}',
                                            ensureDefaults() {
                                                if (!this.isWorking) return;
                                                if (this.startTime !== '' && this.endTime !== '') return;
                                                const s = (this.baselineStart !== '' ? this.baselineStart : this.deptStart);
                                                const e = (this.baselineEnd !== '' ? this.baselineEnd : this.deptEnd);
                                                if (this.startTime === '' && s !== '') this.startTime = s;
                                                if (this.endTime === '' && e !== '') this.endTime = e;
                                            }
                                        }"
                                        x-init="ensureDefaults()"
                                    >
                                        @csrf
                                        @method('PATCH')

                                        <input type="hidden" name="redirect_to" value="{{ url()->full() }}" />
                                        <input type="hidden" name="work_date" value="{{ $d['key'] }}" />

                                        <div class="flex items-start justify-between gap-3">
                                            <div>
                                                <div class="text-lg font-medium text-gray-900">{{ $d['date']->format('Y-m-d') }}</div>
                                                <div class="text-sm text-gray-600">{{ $d['dow'] }}</div>
                                            </div>
                                            @if ($d['has_override'])
                                                <div class="text-xs px-2 py-1 rounded-full bg-indigo-100 text-indigo-700">Override</div>
                                            @else
                                                <div class="text-xs px-2 py-1 rounded-full bg-gray-100 text-gray-700">Default</div>
                                            @endif
                                        </div>

                                        <div class="rounded-lg border border-gray-200 bg-gray-50 p-3">
                                            <div class="text-xs font-semibold text-gray-700">Baseline</div>
                                            <div class="text-xs text-gray-600">
                                                {{ $d['baseline_working'] && $d['baseline_start'] && $d['baseline_end'] ? ($d['baseline_start'] . ' - ' . $d['baseline_end']) : 'Off' }}
                                            </div>
                                            <div class="mt-1 text-[11px] text-gray-500">Department hours: {{ $deptStart }} - {{ $deptEnd }}</div>
                                        </div>

                                        @if (!empty($d['ot']))
                                            <div class="rounded-lg border border-amber-200 bg-amber-50 p-3">
                                                <div class="text-xs font-semibold text-amber-900">Approved OT</div>
                                                <div class="mt-1 space-y-1">
                                                    @foreach ($d['ot'] as $ot)
                                                        <div class="text-xs text-amber-900">
                                                            <span class="font-semibold">{{ $ot['label'] ?? 'OT' }}</span>
                                                            @if (!empty($ot['reason']))
                                                                <span class="text-amber-800">- {{ $ot['reason'] }}</span>
                                                            @endif
                                                        </div>
                                                    @endforeach
                                                </div>
                                            </div>
                                        @endif

                                        <div class="space-y-3">
                                            <label class="inline-flex items-center gap-2">
                                                <input type="checkbox" name="is_working" value="1" class="rounded border-gray-300" x-model="isWorking" x-on:change="ensureDefaults()">
                                                <span class="text-sm text-gray-700">Working</span>
                                            </label>

                                            <div class="grid grid-cols-1 md:grid-cols-2 gap-3" x-show="isWorking" x-cloak>
                                                <div>
                                                    <div class="text-xs text-gray-600 mb-1">Start</div>
                                                    <input type="time" name="start_time" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm w-full" x-model="startTime" />
                                                </div>
                                                <div>
                                                    <div class="text-xs text-gray-600 mb-1">End</div>
                                                    <input type="time" name="end_time" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm w-full" x-model="endTime" />
                                                </div>
                                            </div>

                                            <div class="text-xs text-gray-600">
                                                To revert to baseline, keep Working checked and clear Start/End.
                                            </div>
                                        </div>

                                        <div class="mt-6 flex justify-end gap-2">
                                            <x-secondary-button x-on:click="$dispatch('close-modal', 'edit-override-{{ $d['key'] }}')">Cancel</x-secondary-button>
                                            <x-primary-button>Save</x-primary-button>
                                        </div>
                                    </form>
                                </x-modal>
                            @endforeach
                        </div>
                    </div>

                        </div>
                    </div>
        </div>
    </div>
</x-app-layout>
