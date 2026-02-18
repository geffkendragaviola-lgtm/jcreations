<?php

namespace App\Http\Controllers;

use App\Models\AttendanceDailySummary;
use App\Models\AttendanceImportBatch;
use App\Models\AttendancePeriodSummary;
use App\Models\Employee;
use App\Models\EmployeeScheduleOverride;
use App\Models\LeaveRequest;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

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

        $employees = Employee::query()
            ->with([
                'department',
                'schedules',
                'scheduleOverrides' => fn ($q) => $q->whereDate('work_date', '>=', $start)->whereDate('work_date', '<=', $end),
            ])
            ->whereNotNull('employee_code')
            ->where('employee_code', '!=', '')
            ->orderBy('employee_code')
            ->get();

        $employeeCodes = $employees->pluck('employee_code')
            ->filter()
            ->unique()
            ->values();

        $employeeIdByCode = $employees->pluck('id', 'employee_code')->all();

        $employeeIds = array_values(array_unique(array_filter(array_values($employeeIdByCode))));

        $leaveRequests = LeaveRequest::query()
            ->where('status', 'approved')
            ->whereIn('employee_id', $employeeIds)
            ->whereDate('end_date', '>=', $start)
            ->whereDate('start_date', '<=', $end)
            ->get();

        $leaveDatesByEmployeeCode = [];
        foreach ($leaveRequests as $lr) {
            $employeeCode = array_search((int) $lr->employee_id, $employeeIdByCode, true);
            if (!is_string($employeeCode) || $employeeCode === '') {
                continue;
            }

            $startDate = Carbon::parse($lr->start_date)->startOfDay();
            $endDate = Carbon::parse($lr->end_date)->startOfDay();

            for ($d = $startDate->copy(); $d->lte($endDate); $d->addDay()) {
                $dateStr = $d->format('Y-m-d');
                $leaveDatesByEmployeeCode[$employeeCode][$dateStr] = [
                    'leave_type' => (string) ($lr->leave_type ?? ''),
                    'day_type' => (string) ($lr->day_type ?? 'full_day'),
                    'duration_days' => (float) ($lr->duration_days ?? 0),
                ];
            }
        }

        $dailyByEmployeeDate = [];
        foreach ($daily as $d) {
            $dateStr = optional($d->summary_date)->format('Y-m-d');
            if (!$dateStr) {
                continue;
            }
            $dailyByEmployeeDate[$d->employee_code][$dateStr] = (string) ($d->status ?? '');
        }

        $computedAbsencesByEmployeeCode = [];
        $computedAbsenceDatesByEmployeeCode = [];
        $computedLeaveByEmployeeCode = [];
        $computedDaysWorkedByEmployeeCode = [];

        $employeeByCode = $employees->keyBy('employee_code');

        $startC = Carbon::parse($start)->startOfDay();
        $endC = Carbon::parse($end)->startOfDay();

        $buildExpectedWorkingDates = function (Employee $emp) use ($startC, $endC) {
            $weekly = $emp->schedules->keyBy('day_of_week');
            $hasWeekly = $weekly->count() > 0;

            $overrideMap = $emp->scheduleOverrides
                ->filter(fn ($o) => $o->work_date)
                ->keyBy(fn ($o) => $o->work_date->format('Y-m-d'));

            $expected = [];
            for ($d = $startC->copy(); $d->lte($endC); $d->addDay()) {
                $key = $d->format('Y-m-d');
                $dow = $d->format('l');

                $isWorking = false;
                if ($hasWeekly) {
                    $isWorking = $weekly->has($dow);
                } else {
                    $isWorking = !$d->isSaturday() && !$d->isSunday();
                }

                $override = $overrideMap->get($key);
                if ($override instanceof EmployeeScheduleOverride) {
                    $isWorking = (bool) $override->is_working;
                }

                if ($isWorking) {
                    $expected[$key] = true;
                }
            }
            return $expected;
        };

        foreach ($employeeCodes as $code) {
            $absences = 0.0;
            $absenceDates = [];
            $paidLeaveDays = 0.0;
            $unpaidLeaveDays = 0.0;
            $daysWorked = 0;

            $absenceValueForStatus = function ($status): float {
                if ($status === null) {
                    return 0.0;
                }

                $s = strtolower(trim((string) $status));
                if ($s === '') {
                    return 0.0;
                }

                if ($s === 'absent' || $s === 'whole day absent') {
                    return 1.0;
                }

                if (str_contains($s, 'absent am') || str_contains($s, 'absent pm') || str_contains($s, 'half day (incomplete')) {
                    return 0.5;
                }

                return 0.0;
            };

            $emp = $employeeByCode->get($code);
            if (!$emp) {
                $computedAbsencesByEmployeeCode[$code] = 0;
                $computedLeaveByEmployeeCode[$code] = ['paid' => 0, 'unpaid' => 0];
                $computedDaysWorkedByEmployeeCode[$code] = 0;
                continue;
            }

            $expectedWorking = $buildExpectedWorkingDates($emp);

            for ($d = $startC->copy(); $d->lte($endC); $d->addDay()) {
                $dateStr = $d->format('Y-m-d');
                $isExpected = isset($expectedWorking[$dateStr]);

                $leaveMeta = $leaveDatesByEmployeeCode[$code][$dateStr] ?? null;
                if ($leaveMeta) {
                    $duration = (string) ($leaveMeta['day_type'] ?? 'full_day') === 'half_day' ? 0.5 : 1.0;
                    $leaveType = Str::lower(trim((string) ($leaveMeta['leave_type'] ?? '')));
                    if ($leaveType !== '' && Str::contains($leaveType, 'without pay')) {
                        $unpaidLeaveDays += $duration;
                    } else {
                        $paidLeaveDays += $duration;
                    }
                    continue;
                }

                $status = $dailyByEmployeeDate[$code][$dateStr] ?? null;

                if ($isExpected) {
                    if ($status === null) {
                        $absences += 1.0;
                        $absenceDates[] = $dateStr;
                        continue;
                    }

                    $absenceValue = $absenceValueForStatus($status);
                    if ($absenceValue > 0) {
                        $absences += $absenceValue;
                        $absenceDates[] = $dateStr;
                    } else {
                        $daysWorked += 1;
                    }
                } else {
                    // Rest day work: count as worked only if there is a log/summary and not an absence.
                    if ($status !== null && $absenceValueForStatus($status) <= 0) {
                        $daysWorked += 1;
                    }
                }
            }

            $computedAbsencesByEmployeeCode[$code] = $absences;
            $computedAbsenceDatesByEmployeeCode[$code] = array_values(array_unique($absenceDates));
            $computedLeaveByEmployeeCode[$code] = [
                'paid' => $paidLeaveDays,
                'unpaid' => $unpaidLeaveDays,
            ];
            $computedDaysWorkedByEmployeeCode[$code] = $daysWorked;
        }

        $periodByEmployeeCode = $period->keyBy('employee_code');

        return response()->json([
            'batch_uuid' => $batch?->uuid,
            'date_range' => ['start' => $start, 'end' => $end],
            'daily' => $daily->map(function ($d) {
                $lateMinutes = (int) $d->late_in_minutes + (int) $d->late_break_in_minutes;
                $undertimeMinutes = (int) $d->undertime_break_out_minutes;
                $otMinutes = (int) $d->ot_minutes;

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
                    'late_hours' => round($lateMinutes / 60, 2),
                    'undertime_hours' => round($undertimeMinutes / 60, 2),
                    'ot_hours' => round($otMinutes / 60, 2),
                    'total_hours' => (float) $d->total_hours,
                    'missed_logs' => (int) $d->missed_logs,
                    'status' => $d->status,
                ];
            })->values(),
            'period' => $employees->map(function ($emp) use ($periodByEmployeeCode, $computedAbsencesByEmployeeCode, $computedAbsenceDatesByEmployeeCode, $computedLeaveByEmployeeCode, $computedDaysWorkedByEmployeeCode, $start, $end) {
                $employeeCode = (string) ($emp->employee_code ?? '');
                $p = $periodByEmployeeCode->get($employeeCode);

                $lateFrequency = (int) ($p?->late_frequency ?? 0);
                $missedLogsCount = (int) ($p?->missed_logs_count ?? 0);
                $graceDays = (int) ($p?->grace_days ?? 0);
                $lateDuration = (int) ($p?->late_duration ?? 0);
                $avgLate = (float) ($p?->avg_late_per_occurrence ?? 0);
                $totalUndertime = (int) ($p?->total_undertime ?? 0);
                $undertimeFrequency = (int) ($p?->undertime_frequency ?? 0);
                $mostFrequentLateTime = $p?->most_frequent_late_time;

                return [
                    'employee_code' => $employeeCode,
                    'employee_name' => $emp->full_name ?? null,
                    'department' => $emp->department?->name ?? null,
                    'period_start' => $start,
                    'period_end' => $end,
                    'late_frequency' => $lateFrequency,
                    'missed_logs_count' => $missedLogsCount,
                    'grace_days' => $graceDays,
                    'absences' => (float) ($computedAbsencesByEmployeeCode[$employeeCode] ?? 0),
                    'absence_dates' => array_values($computedAbsenceDatesByEmployeeCode[$employeeCode] ?? []),
                    'days_worked' => (int) ($computedDaysWorkedByEmployeeCode[$employeeCode] ?? (int) ($p?->days_worked ?? 0)),
                    'late_duration' => $lateDuration,
                    'avg_late_per_occurrence' => $avgLate,
                    'total_undertime' => $totalUndertime,
                    'undertime_frequency' => $undertimeFrequency,
                    'most_frequent_late_time' => $mostFrequentLateTime,
                    'leave_paid_days' => (float) ($computedLeaveByEmployeeCode[$employeeCode]['paid'] ?? 0),
                    'leave_unpaid_days' => (float) ($computedLeaveByEmployeeCode[$employeeCode]['unpaid'] ?? 0),
                ];
            })->values(),
        ]);
    }
}
