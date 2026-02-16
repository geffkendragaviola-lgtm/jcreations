<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Time Tracking Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                        <div class="p-4 rounded border bg-gray-50">
                            <div class="text-sm text-gray-600">Present Today</div>
                            <div class="mt-1 text-2xl font-semibold">{{ (int) ($metrics['present_today'] ?? 0) }}</div>
                        </div>
                        <div class="p-4 rounded border bg-gray-50">
                            <div class="text-sm text-gray-600">Late Today</div>
                            <div class="mt-1 text-2xl font-semibold">{{ (int) ($metrics['late_today'] ?? 0) }}</div>
                        </div>
                        <div class="p-4 rounded border bg-gray-50">
                            <div class="text-sm text-gray-600">Absent Today</div>
                            <div class="mt-1 text-2xl font-semibold">{{ (int) ($metrics['absent_today'] ?? 0) }}</div>
                        </div>
                        <div class="p-4 rounded border bg-gray-50">
                            <div class="text-sm text-gray-600">Total Hours Today</div>
                            <div class="mt-1 text-2xl font-semibold">{{ number_format((float) ($metrics['total_hours_today'] ?? 0), 2) }}</div>
                        </div>
                    </div>

                    <div class="mt-6 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                        <div class="p-4 rounded border bg-gray-50">
                            <div class="text-sm text-gray-600">Monthly Lates</div>
                            <div class="mt-1 text-2xl font-semibold">{{ (int) ($metrics['monthly_lates'] ?? 0) }}</div>
                        </div>
                        <div class="p-4 rounded border bg-gray-50">
                            <div class="text-sm text-gray-600">Monthly Absences</div>
                            <div class="mt-1 text-2xl font-semibold">{{ (int) ($metrics['monthly_absences'] ?? 0) }}</div>
                        </div>
                        <div class="p-4 rounded border bg-gray-50">
                            <div class="text-sm text-gray-600">Overtime Hours</div>
                            <div class="mt-1 text-2xl font-semibold">{{ number_format((float) ($metrics['overtime_hours'] ?? 0), 2) }}</div>
                        </div>
                        <div class="p-4 rounded border bg-gray-50">
                            <div class="text-sm text-gray-600">On-Time %</div>
                            <div class="mt-1 text-2xl font-semibold">{{ number_format((float) ($metrics['on_time_percent'] ?? 0), 2) }}%</div>
                        </div>
                    </div>

                    <div class="mt-6">
                        <a href="{{ route('time-tracking.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                            {{ __('Open Time Tracking') }}
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
