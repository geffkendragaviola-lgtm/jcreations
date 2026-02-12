<x-app-layout>
    <x-slot name="header">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight mb-0">
                {{ __('Attendance Logs') }}
            </h2>
            <a href="{{ route('time-tracking.index') }}" class="btn btn-sm btn-outline-primary">
                Back to Time Tracking
            </a>
        </div>
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
