<?php

namespace App\Http\Controllers;

use App\Models\AttendanceDailySummary;
use App\Models\AttendanceImportBatch;
use App\Models\Employee;
use App\Models\LeaveRequest;
use App\Models\LateRequest;
use App\Models\OvertimeRequest;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class PayrollController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        if (!$user?->canManageBackoffice()) {
            abort(403);
        }

        $mode = trim((string) $request->input('mode', 'custom'));
        if (!in_array($mode, ['weekly', 'biweekly', 'monthly', 'custom'], true)) {
            $mode = 'custom';
        }

        $baseHoursPerDay = (float) $request->input('base_hours_per_day', 8);
        if ($baseHoursPerDay <= 0) {
            $baseHoursPerDay = 8;
        }

        $otMultiplier = (float) $request->input('ot_multiplier', 1);
        if ($otMultiplier <= 0) {
            $otMultiplier = 1;
        }

        $batches = AttendanceImportBatch::query()
            ->orderByDesc('date_end')
            ->orderByDesc('id')
            ->limit(200)
            ->get();

        $batchUuid = trim((string) $request->input('batch_uuid', ''));
        $selectedBatch = null;
        if ($batchUuid !== '') {
            $selectedBatch = $batches->firstWhere('uuid', $batchUuid);
        }
        if (!$selectedBatch && $batches->count() > 0) {
            $selectedBatch = $batches->first();
            $batchUuid = (string) $selectedBatch->uuid;
        }

        [$start, $end] = $this->resolveRange($request, $mode);

        if ($selectedBatch) {
            $start = optional($selectedBatch->date_start)->format('Y-m-d');
            $end = optional($selectedBatch->date_end)->format('Y-m-d');
        }

        $rows = [];

        if ($start && $end) {
            $employees = Employee::query()
                ->with([
                    'department',
                    'schedules',
                    'scheduleOverrides' => fn ($q) => $q->whereDate('work_date', '>=', $start)->whereDate('work_date', '<=', $end),
                ])
                ->whereHas('roles', function ($q) {
                    $q->where('name', 'employee');
                })
                ->orderBy('employee_code')
                ->get();

            $daily = AttendanceDailySummary::query()
                ->when($selectedBatch, fn ($q) => $q->where('import_batch_id', $selectedBatch->id))
                ->whereDate('summary_date', '>=', $start)
                ->whereDate('summary_date', '<=', $end)
                ->get()
                ->groupBy('employee_code');

            $approvedOt = OvertimeRequest::query()
                ->where('status', 'approved')
                ->whereDate('date', '>=', $start)
                ->whereDate('date', '<=', $end)
                ->get()
                ->groupBy('employee_id');

            $approvedLeave = LeaveRequest::query()
                ->where('status', 'approved')
                ->whereDate('end_date', '>=', $start)
                ->whereDate('start_date', '<=', $end)
                ->get()
                ->groupBy('employee_id');

            $approvedLate = LateRequest::query()
                ->where('status', 'approved')
                ->whereDate('date', '>=', $start)
                ->whereDate('date', '<=', $end)
                ->get()
                ->groupBy('employee_id');

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
                    if ($override) {
                        $isWorking = (bool) $override->is_working;
                    }

                    if ($isWorking) {
                        $expected[$key] = true;
                    }
                }

                return $expected;
            };

            $buildLeaveDates = function ($leaveRequests) use ($startC, $endC) {
                $leaveByDate = [];

                foreach ($leaveRequests as $lr) {
                    if (!$lr->start_date || !$lr->end_date) {
                        continue;
                    }

                    $from = Carbon::parse($lr->start_date)->startOfDay();
                    $to = Carbon::parse($lr->end_date)->startOfDay();

                    if ($to->lt($startC) || $from->gt($endC)) {
                        continue;
                    }

                    if ($from->lt($startC)) {
                        $from = $startC->copy();
                    }
                    if ($to->gt($endC)) {
                        $to = $endC->copy();
                    }

                    for ($d = $from->copy(); $d->lte($to); $d->addDay()) {
                        $key = $d->format('Y-m-d');
                        $leaveByDate[$key] = [
                            'leave_type' => (string) ($lr->leave_type ?? ''),
                            'day_type' => (string) ($lr->day_type ?? 'full_day'),
                        ];
                    }
                }

                return $leaveByDate;
            };

            foreach ($employees as $emp) {
                $dailyRate = (float) ($emp->daily_rate ?? 0);
                $hourlyRate = $baseHoursPerDay > 0 ? ($dailyRate / $baseHoursPerDay) : 0;

                $empDaily = $daily->get($emp->employee_code, collect());

                $expectedWorking = $buildExpectedWorkingDates($emp);

                $leaveByDate = $buildLeaveDates($approvedLeave->get($emp->id, collect()));

                $paidLeaveDays = 0.0;
                $unpaidLeaveDays = 0.0;
                foreach ($leaveByDate as $meta) {
                    $duration = (string) ($meta['day_type'] ?? 'full_day') === 'half_day' ? 0.5 : 1.0;
                    $leaveType = Str::lower(trim((string) ($meta['leave_type'] ?? '')));
                    if ($leaveType !== '' && Str::contains($leaveType, 'without pay')) {
                        $unpaidLeaveDays += $duration;
                    } else {
                        $paidLeaveDays += $duration;
                    }
                }

                $summariesByDate = $empDaily
                    ->filter(fn ($d) => $d && $d->summary_date)
                    ->keyBy(fn ($d) => $d->summary_date->format('Y-m-d'));

                $regularWorkedDays = 0;
                $restDayWorkedDays = 0;
                $unpaidAbsenceDays = 0;

                for ($d = $startC->copy(); $d->lte($endC); $d->addDay()) {
                    $dateKey = $d->format('Y-m-d');
                    $isExpected = isset($expectedWorking[$dateKey]);

                    if (isset($leaveByDate[$dateKey])) {
                        continue;
                    }

                    $summary = $summariesByDate->get($dateKey);
                    $status = $summary ? (string) ($summary->status ?? '') : null;

                    if ($isExpected) {
                        if ($status === null) {
                            $unpaidAbsenceDays += 1;
                            continue;
                        }
                        if (strtoupper($status) !== 'ABSENT') {
                            $regularWorkedDays += 1;
                        } else {
                            $unpaidAbsenceDays += 1;
                        }
                    } else {
                        if ($status !== null && strtoupper((string) $status) !== 'ABSENT') {
                            $restDayWorkedDays += 1;
                        }
                    }
                }

                $daysWorked = $regularWorkedDays + $restDayWorkedDays;

                $lateMinutes = (int) $empDaily->sum(fn ($d) => (int) $d->late_in_minutes + (int) $d->late_break_in_minutes);
                $undertimeMinutes = (int) $empDaily->sum('undertime_break_out_minutes');

                $waivedLateMinutes = (int) ($approvedLate->get($emp->id, collect())
                    ->where('type', 'late')
                    ->sum(fn ($r) => (int) ($r->minutes ?? 0)));

                $waivedUndertimeMinutes = (int) ($approvedLate->get($emp->id, collect())
                    ->where('type', 'undertime')
                    ->sum(fn ($r) => (int) ($r->minutes ?? 0)));

                $lateMinutes = max($lateMinutes - $waivedLateMinutes, 0);
                $undertimeMinutes = max($undertimeMinutes - $waivedUndertimeMinutes, 0);

                $lateHours = round($lateMinutes / 60, 2);
                $undertimeHours = round($undertimeMinutes / 60, 2);

                $otHours = round(((float) $approvedOt->get($emp->id, collect())->sum('hours')), 2);

                $grossRegular = round($regularWorkedDays * $dailyRate, 2);
                $grossPaidLeave = round($paidLeaveDays * $dailyRate, 2);
                $grossRestDay = round($restDayWorkedDays * $dailyRate * 1.3, 2);

                $baseWorkingDaysForPeriod = $regularWorkedDays + $paidLeaveDays + $unpaidLeaveDays + $unpaidAbsenceDays;
                $grossBase = round($baseWorkingDaysForPeriod * $dailyRate, 2);
                $gross = round($grossBase + $grossRestDay, 2);
                $lateDeduction = round($lateHours * $hourlyRate, 2);
                $undertimeDeduction = round($undertimeHours * $hourlyRate, 2);
                $absenceDeduction = round(($unpaidAbsenceDays + $unpaidLeaveDays) * $dailyRate, 2);

                $otPay = round($otHours * $hourlyRate * $otMultiplier, 2);

                $sss = (float) ($emp->sss_deduction ?? 0);

                $pagibig = (float) ($emp->pagibig_deduction ?? 0);

                $philhealth = (float) ($emp->philhealth_deduction ?? 0);

                $gov = $sss + $pagibig + $philhealth;

                $cashAdvance = $request->input('cash_advance_deduction.' . $emp->id);
                $cashAdvance = $cashAdvance !== null ? (float) $cashAdvance : (float) ($emp->cash_advance_deduction ?? 0);

                $totalDeductions = $gov + $cashAdvance;

                $net = round($gross + $otPay - $lateDeduction - $undertimeDeduction - $absenceDeduction - $totalDeductions, 2);

                $rows[] = [
                    'employee_id' => $emp->id,
                    'employee_code' => $emp->employee_code,
                    'employee_name' => $emp->full_name,
                    'department' => $emp->department?->name,
                    'range_start' => $start,
                    'range_end' => $end,
                    'daily_rate' => $dailyRate,
                    'hourly_rate' => round($hourlyRate, 2),
                    'days_worked' => $daysWorked,
                    'regular_worked_days' => $regularWorkedDays,
                    'rest_day_worked_days' => $restDayWorkedDays,
                    'paid_leave_days' => $paidLeaveDays,
                    'unpaid_leave_days' => $unpaidLeaveDays,
                    'unpaid_absence_days' => $unpaidAbsenceDays,
                    'late_hours' => $lateHours,
                    'undertime_hours' => $undertimeHours,
                    'approved_ot_hours' => $otHours,
                    'approved_absence_days' => $unpaidLeaveDays,
                    'gross_pay' => $gross,
                    'ot_pay' => $otPay,
                    'late_deduction' => $lateDeduction,
                    'undertime_deduction' => $undertimeDeduction,
                    'absence_deduction' => $absenceDeduction,
                    'government_deduction' => round($gov, 2),
                    'sss_deduction' => round($sss, 2),
                    'pagibig_deduction' => round($pagibig, 2),
                    'philhealth_deduction' => round($philhealth, 2),
                    'cash_advance_deduction' => round($cashAdvance, 2),
                    'fixed_deductions_total' => round($totalDeductions, 2),
                    'total_government_deductions' => round($totalDeductions, 2),
                    'net_pay' => $net,
                ];
            }

            if ($request->isMethod('post') && (string) $request->input('save_government_deduction', '') === '1') {
                foreach ($rows as $r) {
                    Employee::query()->where('id', $r['employee_id'])->update([
                        'cash_advance_deduction' => $r['cash_advance_deduction'],
                    ]);
                }
            }
        }

        return view('payroll.index', [
            'mode' => $mode,
            'start' => $start,
            'end' => $end,
            'base_hours_per_day' => $baseHoursPerDay,
            'ot_multiplier' => $otMultiplier,
            'batches' => $batches,
            'batch_uuid' => $batchUuid,
            'rows' => $rows,
        ]);
    }

    private function resolveRange(Request $request, string $mode): array
    {
        $today = Carbon::today();

        if ($mode === 'weekly') {
            $start = $today->copy()->startOfWeek(Carbon::MONDAY);
            $end = $start->copy()->addDays(6);
            return [$start->toDateString(), $end->toDateString()];
        }

        if ($mode === 'biweekly') {
            $start = $today->copy()->startOfWeek(Carbon::MONDAY);
            $end = $start->copy()->addDays(13);
            return [$start->toDateString(), $end->toDateString()];
        }

        if ($mode === 'monthly') {
            $start = $today->copy()->startOfMonth();
            $end = $today->copy()->endOfMonth();
            return [$start->toDateString(), $end->toDateString()];
        }

        $start = trim((string) $request->input('start', ''));
        $end = trim((string) $request->input('end', ''));

        if ($start === '' || $end === '') {
            return [null, null];
        }

        return [$start, $end];
    }

    private function countApprovedLeaveDaysInRange($leaveRequests, string $start, string $end): float
    {
        $startC = Carbon::parse($start)->startOfDay();
        $endC = Carbon::parse($end)->startOfDay();

        $days = 0;
        foreach ($leaveRequests as $lr) {
            $s = Carbon::parse($lr->start_date)->startOfDay();
            $e = Carbon::parse($lr->end_date)->startOfDay();

            if ($e->lt($startC) || $s->gt($endC)) {
                continue;
            }

            $from = $s->lt($startC) ? $startC : $s;
            $to = $e->gt($endC) ? $endC : $e;

            if ($lr->day_type === 'half_day') {
                $days += 0.5;
                continue;
            }

            if ($lr->duration_days !== null && (float) $lr->duration_days > 0) {
                $days += (float) $lr->duration_days;
                continue;
            }

            $days += $from->diffInDays($to) + 1;
        }

        return (float) $days;
    }
}
