<?php

namespace App\Http\Controllers;

use App\Models\AttendanceDailySummary;
use App\Models\AttendanceImportBatch;
use App\Models\AttendancePeriodSummary;
use Illuminate\Http\Request;

class AttendanceSummaryController extends Controller
{
    public function index(Request $request)
    {
        $batchUuid = trim((string) $request->query('batch', ''));
        $batch = null;

        if ($batchUuid !== '') {
            $batch = AttendanceImportBatch::query()->where('uuid', $batchUuid)->first();
            if (!$batch) {
                return response()->json(['message' => 'Batch not found.'], 404);
            }
        }

        if ($batch) {
            $start = optional($batch->date_start)->format('Y-m-d');
            $end = optional($batch->date_end)->format('Y-m-d');
        } else {
            $validated = $request->validate([
                'start' => ['required', 'date'],
                'end' => ['required', 'date'],
            ]);

            $start = $validated['start'];
            $end = $validated['end'];
        }

        $daily = AttendanceDailySummary::query()
            ->with(['employee.department'])
            ->when($batch, fn ($q) => $q->where('import_batch_id', $batch->id))
            ->whereDate('summary_date', '>=', $start)
            ->whereDate('summary_date', '<=', $end)
            ->orderBy('summary_date')
            ->orderBy('employee_code')
            ->get();

        $period = AttendancePeriodSummary::query()
            ->with(['employee.department'])
            ->when($batch, fn ($q) => $q->where('import_batch_id', $batch->id))
            ->whereDate('period_start', $start)
            ->whereDate('period_end', $end)
            ->orderBy('employee_code')
            ->get();

        return response()->json([
            'batch_uuid' => $batch?->uuid,
            'date_range' => ['start' => $start, 'end' => $end],
            'daily' => $daily->map(function ($d) {
                return [
                    'employee_code' => $d->employee_code,
                    'employee_name' => $d->employee?->full_name ?? null,
                    'department' => $d->employee?->department?->name ?? null,
                    'summary_date' => optional($d->summary_date)->format('Y-m-d'),
                    'time_in' => $d->time_in,
                    'break_out' => $d->break_out,
                    'break_in' => $d->break_in,
                    'time_out' => $d->time_out,
                    'grace_used' => (bool) $d->grace_used,
                    'late_in_minutes' => (int) $d->late_in_minutes,
                    'undertime_break_out_minutes' => (int) $d->undertime_break_out_minutes,
                    'late_break_in_minutes' => (int) $d->late_break_in_minutes,
                    'ot_minutes' => (int) $d->ot_minutes,
                    'total_hours' => (float) $d->total_hours,
                    'missed_logs' => (int) $d->missed_logs,
                    'status' => $d->status,
                ];
            })->values(),
            'period' => $period->map(function ($p) {
                return [
                    'employee_code' => $p->employee_code,
                    'employee_name' => $p->employee?->full_name ?? null,
                    'department' => $p->employee?->department?->name ?? null,
                    'period_start' => optional($p->period_start)->format('Y-m-d'),
                    'period_end' => optional($p->period_end)->format('Y-m-d'),
                    'late_frequency' => (int) $p->late_frequency,
                    'missed_logs_count' => (int) $p->missed_logs_count,
                    'grace_days' => (int) $p->grace_days,
                    'absences' => (int) $p->absences,
                    'days_worked' => (int) $p->days_worked,
                    'late_duration' => (int) $p->late_duration,
                    'avg_late_per_occurrence' => (float) $p->avg_late_per_occurrence,
                    'total_undertime' => (int) $p->total_undertime,
                    'undertime_frequency' => (int) $p->undertime_frequency,
                    'most_frequent_late_time' => $p->most_frequent_late_time,
                ];
            })->values(),
        ]);
    }
}
