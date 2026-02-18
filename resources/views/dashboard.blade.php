<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            {{-- Today's Overview --}}
            <div>
                <h3 class="text-sm font-semibold text-gray-500 uppercase tracking-wider mb-3">Today's Overview</h3>
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                    <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-5 flex items-start gap-4">
                        <div class="w-11 h-11 rounded-lg bg-emerald-100 flex items-center justify-center shrink-0">
                            <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        </div>
                        <div>
                            <div class="text-xs font-medium text-gray-500 uppercase tracking-wide">Present</div>
                            <div class="mt-1 text-2xl font-bold text-gray-900">{{ (int) ($metrics['present_today'] ?? 0) }}</div>
                        </div>
                    </div>

                    <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-5 flex items-start gap-4">
                        <div class="w-11 h-11 rounded-lg bg-amber-100 flex items-center justify-center shrink-0">
                            <svg class="w-5 h-5 text-amber-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        </div>
                        <div>
                            <div class="text-xs font-medium text-gray-500 uppercase tracking-wide">Late</div>
                            <div class="mt-1 text-2xl font-bold text-gray-900">{{ (int) ($metrics['late_today'] ?? 0) }}</div>
                        </div>
                    </div>

                    <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-5 flex items-start gap-4">
                        <div class="w-11 h-11 rounded-lg bg-red-100 flex items-center justify-center shrink-0">
                            <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        </div>
                        <div>
                            <div class="text-xs font-medium text-gray-500 uppercase tracking-wide">Absent</div>
                            <div class="mt-1 text-2xl font-bold text-gray-900">{{ (int) ($metrics['absent_today'] ?? 0) }}</div>
                        </div>
                    </div>

                    <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-5 flex items-start gap-4">
                        <div class="w-11 h-11 rounded-lg bg-indigo-100 flex items-center justify-center shrink-0">
                            <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        </div>
                        <div>
                            <div class="text-xs font-medium text-gray-500 uppercase tracking-wide">Total Hours</div>
                            <div class="mt-1 text-2xl font-bold text-gray-900">{{ number_format((float) ($metrics['total_hours_today'] ?? 0), 2) }}</div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Monthly Summary --}}
            <div>
                <h3 class="text-sm font-semibold text-gray-500 uppercase tracking-wider mb-3">Monthly Summary</h3>
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                    <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-5 flex items-start gap-4">
                        <div class="w-11 h-11 rounded-lg bg-orange-100 flex items-center justify-center shrink-0">
                            <svg class="w-5 h-5 text-orange-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                        </div>
                        <div>
                            <div class="text-xs font-medium text-gray-500 uppercase tracking-wide">Lates</div>
                            <div class="mt-1 text-2xl font-bold text-gray-900">{{ (int) ($metrics['monthly_lates'] ?? 0) }}</div>
                        </div>
                    </div>

                    <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-5 flex items-start gap-4">
                        <div class="w-11 h-11 rounded-lg bg-rose-100 flex items-center justify-center shrink-0">
                            <svg class="w-5 h-5 text-rose-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/></svg>
                        </div>
                        <div>
                            <div class="text-xs font-medium text-gray-500 uppercase tracking-wide">Absences</div>
                            <div class="mt-1 text-2xl font-bold text-gray-900">{{ (int) ($metrics['monthly_absences'] ?? 0) }}</div>
                        </div>
                    </div>

                    <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-5 flex items-start gap-4">
                        <div class="w-11 h-11 rounded-lg bg-blue-100 flex items-center justify-center shrink-0">
                            <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/></svg>
                        </div>
                        <div>
                            <div class="text-xs font-medium text-gray-500 uppercase tracking-wide">Overtime Hrs</div>
                            <div class="mt-1 text-2xl font-bold text-gray-900">{{ number_format((float) ($metrics['overtime_hours'] ?? 0), 2) }}</div>
                        </div>
                    </div>

                    <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-5 flex items-start gap-4">
                        <div class="w-11 h-11 rounded-lg bg-teal-100 flex items-center justify-center shrink-0">
                            <svg class="w-5 h-5 text-teal-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                        </div>
                        <div>
                            <div class="text-xs font-medium text-gray-500 uppercase tracking-wide">On-Time Rate</div>
                            <div class="mt-1 text-2xl font-bold text-gray-900">{{ number_format((float) ($metrics['on_time_percent'] ?? 0), 1) }}%</div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Quick Action --}}
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6 flex flex-col sm:flex-row items-start sm:items-center gap-4 sm:justify-between">
                <div>
                    <h3 class="text-base font-bold text-gray-900">Time Tracking</h3>
                    <p class="text-sm text-gray-500 mt-0.5">View detailed attendance records and upload CSV data</p>
                </div>
                <a href="{{ route('time-tracking.index') }}" class="inline-flex items-center gap-2 px-5 py-2.5 bg-indigo-600 text-white text-sm font-semibold rounded-lg shadow-sm hover:bg-indigo-700 transition shrink-0">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    Open Time Tracking
                </a>
            </div>
        </div>
    </div>
</x-app-layout>
