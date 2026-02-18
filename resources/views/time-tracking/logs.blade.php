<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('Time Tracking') }}</h2>
    </x-slot>

    <x-slot name="headerNav">
        <nav class="flex gap-6 -mb-px overflow-x-auto">
            <a href="{{ route('time-tracking.index') }}" class="inline-flex items-center px-1 py-3 border-b-2 text-sm font-medium {{ request()->routeIs('time-tracking.index') ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">Time Tracking</a>
            <a href="{{ route('time-tracking.logs') }}" class="inline-flex items-center px-1 py-3 border-b-2 text-sm font-medium {{ request()->routeIs('time-tracking.logs') ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">Logs</a>
            <a href="{{ route('work-schedules.index') }}" class="inline-flex items-center px-1 py-3 border-b-2 text-sm font-medium {{ request()->routeIs('work-schedules.*') ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">Schedule</a>
        </nav>
    </x-slot>

    @push('styles')
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    @endpush

    <div class="py-6">
        <div class="container-fluid py-4">
            <div class="card mb-3">
                <div class="card-body">
                    <form method="GET" action="{{ route('time-tracking.logs') }}" class="row g-2 align-items-end">
                        <div class="col-12 col-md-3">
                            <label class="form-label mb-1">Employee Code</label>
                            <input type="text" name="employee_code" value="{{ $filters['employee_code'] }}" class="form-control form-control-sm" placeholder="e.g. SHOP2025-22" />
                        </div>
                        <div class="col-12 col-md-2">
                            <label class="form-label mb-1">Activity</label>
                            <select name="activity" class="form-select form-select-sm">
                                <option value="" {{ $filters['activity'] === '' ? 'selected' : '' }}>All</option>
                                <option value="in" {{ $filters['activity'] === 'in' ? 'selected' : '' }}>In</option>
                                <option value="out" {{ $filters['activity'] === 'out' ? 'selected' : '' }}>Out</option>
                            </select>
                        </div>
                        <div class="col-12 col-md-2">
                            <label class="form-label mb-1">Date Start</label>
                            <input type="date" name="date_start" value="{{ $filters['date_start'] }}" class="form-control form-control-sm" />
                        </div>
                        <div class="col-12 col-md-2">
                            <label class="form-label mb-1">Date End</label>
                            <input type="date" name="date_end" value="{{ $filters['date_end'] }}" class="form-control form-control-sm" />
                        </div>
                        <div class="col-12 col-md-3 d-flex gap-2">
                            <button type="submit" class="btn btn-sm btn-primary">Filter</button>
                            <a href="{{ route('time-tracking.logs') }}" class="btn btn-sm btn-outline-secondary">Reset</a>
                        </div>
                    </form>
                </div>
            </div>

            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm table-striped align-middle mb-0">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Time</th>
                                    <th>Employee Code</th>
                                    <th>Name</th>
                                    <th>Department</th>
                                    <th>Activity</th>
                                    <th>Address / Remarks</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($logs as $log)
                                    <tr>
                                        <td>{{ optional($log->log_date)->format('Y-m-d') }}</td>
                                        <td>{{ $log->log_time }}</td>
                                        <td>{{ $log->employee_code }}</td>
                                        <td>{{ $log->employee_name_snapshot ?? ($log->employee?->full_name ?? '') }}</td>
                                        <td>{{ $log->department_snapshot }}</td>
                                        <td>{{ strtoupper($log->activity) }}</td>
                                        <td style="max-width: 520px; white-space: normal;">{{ $log->address }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-muted fst-italic">No logs found.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-3">
                        {{ $logs->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
