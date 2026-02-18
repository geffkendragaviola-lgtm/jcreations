<?php

namespace App\Http\Controllers;

use App\Models\AttendanceDailySummary;
use App\Models\AttendanceImportBatch;
use App\Models\Employee;
use App\Models\CashAdvanceRequest;
use App\Models\LeaveRequest;
use App\Models\LateRequest;
use App\Models\LoanPayment;
use App\Models\LoanRequest;
use App\Models\OvertimeRequest;
use App\Models\PayrollItem;
use App\Models\PayrollRun;
use App\Services\ActivityLogger;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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
            $periodStart = Carbon::parse($start)->startOfDay()->toDateTimeString();

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

            $cashAdvanceDeductionMap = CashAdvanceRequest::query()
                ->selectRaw('employee_id, SUM(COALESCE(deduction_amount, amount)) AS total')
                ->where('status', 'approved')
                ->whereNotNull('approved_at')
                ->whereNotNull('released_at')
                ->where('approved_at', '<', $periodStart)
                ->where('released_at', '<', $periodStart)
                ->whereNull('deducted_payroll_run_id')
                ->groupBy('employee_id')
                ->pluck('total', 'employee_id');

            $loanDeductionMap = LoanRequest::query()
                ->where('status', 'approved')
                ->where('loan_status', 'active')
                ->whereNotNull('approved_at')
                ->whereNotNull('released_at')
                ->where('approved_at', '<', $periodStart)
                ->where('released_at', '<', $periodStart)
                ->where(function ($q) {
                    $q->whereNull('remaining_balance')->orWhere('remaining_balance', '>', 0);
                })
                ->get()
                ->groupBy('employee_id')
                ->map(function ($loans) {
                    $total = 0.0;
                    foreach ($loans as $loan) {
                        $monthly = (float) ($loan->monthly_amortization ?? 0);
                        $remaining = (float) ($loan->remaining_balance ?? $loan->amount ?? 0);
                        if ($monthly <= 0 || $remaining <= 0) {
                            continue;
                        }
                        $total += min($monthly, $remaining);
                    }
                    return round($total, 2);
                });

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

                $regularWorkedDays = 0.0;
                $restDayWorkedDays = 0;
                $unpaidAbsenceDays = 0.0;

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
                            $unpaidAbsenceDays += 1.0;
                            continue;
                        }

                        $absenceValue = $absenceValueForStatus($status);
                        if ($absenceValue > 0) {
                            $unpaidAbsenceDays += $absenceValue;
                            $regularWorkedDays += max(1.0 - $absenceValue, 0.0);
                        } else {
                            $regularWorkedDays += 1.0;
                        }
                    } else {
                        if ($status !== null && $absenceValueForStatus($status) <= 0) {
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

                $gross = round($regularWorkedDays * $dailyRate, 2);
                $otPay = round($otHours * $hourlyRate * $otMultiplier, 2);
                $lateDeduction = round($lateHours * $hourlyRate, 2);
                $undertimeDeduction = round($undertimeHours * $hourlyRate, 2);
                $absenceDeduction = 0.0;

                $sss = (float) ($emp->sss_deduction ?? 0);

                $pagibig = (float) ($emp->pagibig_deduction ?? 0);

                $philhealth = (float) ($emp->philhealth_deduction ?? 0);

                $gov = $sss + $pagibig + $philhealth;

                $defaultCashAdvance = (float) ($cashAdvanceDeductionMap->get($emp->id) ?? 0);
                $cashAdvance = $request->input('cash_advance_deduction.' . $emp->id);
                $cashAdvance = $cashAdvance !== null ? (float) $cashAdvance : $defaultCashAdvance;

                $loanDeduction = (float) ($loanDeductionMap->get($emp->id) ?? 0);

                $totalDeductions = $gov + $cashAdvance + $loanDeduction;

                $net = round($gross + $otPay - $lateDeduction - $undertimeDeduction - $totalDeductions, 2);

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
                    'loan_deduction' => round($loanDeduction, 2),
                    'fixed_deductions_total' => round($totalDeductions, 2),
                    'total_government_deductions' => round($totalDeductions, 2),
                    'net_pay' => $net,
                ];
            }
        }

        $savedRuns = PayrollRun::query()
            ->orderByDesc('id')
            ->limit(50)
            ->get();

        return view('payroll.index', [
            'mode' => $mode,
            'start' => $start,
            'end' => $end,
            'base_hours_per_day' => $baseHoursPerDay,
            'ot_multiplier' => $otMultiplier,
            'batches' => $batches,
            'batch_uuid' => $batchUuid,
            'rows' => $rows,
            'savedRuns' => $savedRuns,
        ]);
    }

    public function save(Request $request): RedirectResponse
    {
        $user = $request->user();
        if (!$user?->canManageBackoffice()) {
            abort(403);
        }

        $validated = $request->validate([
            'name' => ['nullable', 'string', 'max:255'],
            'period_start' => ['required', 'date'],
            'period_end' => ['required', 'date'],
            'mode' => ['required', 'string'],
            'base_hours_per_day' => ['required', 'numeric', 'min:1'],
            'ot_multiplier' => ['required', 'numeric', 'min:0'],
            'batch_id' => ['nullable', 'integer'],
            'batch_uuid' => ['nullable', 'string', 'max:36'],
            'rows' => ['required', 'string'],
        ]);

        $batchId = $validated['batch_id'] ?? null;
        if (!$batchId) {
            $batchUuid = trim((string) ($validated['batch_uuid'] ?? ''));
            if ($batchUuid !== '') {
                $batchId = AttendanceImportBatch::query()->where('uuid', $batchUuid)->value('id');
            }
        }

        $rows = json_decode($validated['rows'], true);
        if (!is_array($rows) || count($rows) === 0) {
            return redirect()->back()->withErrors(['rows' => 'No payroll data to save.']);
        }

        $run = null;

        DB::transaction(function () use ($validated, $batchId, $rows, $user, &$run) {
            $nextRunId = ((int) PayrollRun::query()->max('id')) + 1;

            $run = PayrollRun::create([
                'id' => $nextRunId,
                'uuid' => (string) Str::uuid(),
                'name' => $validated['name'] ?: "Payroll {$validated['period_start']} to {$validated['period_end']}",
                'period_start' => $validated['period_start'],
                'period_end' => $validated['period_end'],
                'mode' => $validated['mode'],
                'base_hours_per_day' => $validated['base_hours_per_day'],
                'ot_multiplier' => $validated['ot_multiplier'],
                'batch_id' => $batchId ?: null,
                'status' => 'draft',
                'created_by' => optional($user->employee)->id,
            ]);

            foreach ($rows as $row) {
                $nextItemId = ((int) PayrollItem::query()->max('id')) + 1;
                PayrollItem::create([
                    'id' => $nextItemId,
                    'payroll_run_id' => $run->id,
                    'employee_id' => $row['employee_id'],
                    'employee_code' => $row['employee_code'] ?? null,
                    'daily_rate' => $row['daily_rate'] ?? 0,
                    'hourly_rate' => $row['hourly_rate'] ?? 0,
                    'regular_worked_days' => $row['regular_worked_days'] ?? 0,
                    'rest_day_worked_days' => $row['rest_day_worked_days'] ?? 0,
                    'paid_leave_days' => $row['paid_leave_days'] ?? 0,
                    'unpaid_leave_days' => $row['unpaid_leave_days'] ?? 0,
                    'unpaid_absence_days' => $row['unpaid_absence_days'] ?? 0,
                    'late_hours' => $row['late_hours'] ?? 0,
                    'undertime_hours' => $row['undertime_hours'] ?? 0,
                    'approved_ot_hours' => $row['approved_ot_hours'] ?? 0,
                    'gross_pay' => $row['gross_pay'] ?? 0,
                    'ot_pay' => $row['ot_pay'] ?? 0,
                    'late_deduction' => $row['late_deduction'] ?? 0,
                    'undertime_deduction' => $row['undertime_deduction'] ?? 0,
                    'absence_deduction' => $row['absence_deduction'] ?? 0,
                    'sss_deduction' => $row['sss_deduction'] ?? 0,
                    'pagibig_deduction' => $row['pagibig_deduction'] ?? 0,
                    'philhealth_deduction' => $row['philhealth_deduction'] ?? 0,
                    'tax_deduction' => 0,
                    'cash_advance_deduction' => $row['cash_advance_deduction'] ?? 0,
                    'loan_deduction' => $row['loan_deduction'] ?? 0,
                    'other_deductions' => 0,
                    'total_deductions' => $row['fixed_deductions_total'] ?? 0,
                    'net_pay' => $row['net_pay'] ?? 0,
                ]);
            }

            $periodStart = Carbon::parse($validated['period_start'])->startOfDay();
            $periodEnd = Carbon::parse($validated['period_end'])->startOfDay();

            foreach ($rows as $row) {
                $employeeId = (int) ($row['employee_id'] ?? 0);
                if ($employeeId <= 0) {
                    continue;
                }

                $cashAdv = CashAdvanceRequest::query()
                    ->lockForUpdate()
                    ->where('employee_id', $employeeId)
                    ->where('status', 'approved')
                    ->whereNotNull('approved_at')
                    ->whereNotNull('released_at')
                    ->where('approved_at', '<', $periodStart)
                    ->where('released_at', '<', $periodStart)
                    ->whereNull('deducted_payroll_run_id')
                    ->get();

                foreach ($cashAdv as $c) {
                    $c->deducted_payroll_run_id = $run->id;
                    $c->deducted_at = now();
                    $c->deduction_amount = $c->deduction_amount ?? $c->amount;
                    $c->save();
                }

                $loans = LoanRequest::query()
                    ->lockForUpdate()
                    ->where('employee_id', $employeeId)
                    ->where('status', 'approved')
                    ->where('loan_status', 'active')
                    ->whereNotNull('approved_at')
                    ->whereNotNull('released_at')
                    ->where('approved_at', '<', $periodStart)
                    ->where('released_at', '<', $periodStart)
                    ->get();

                foreach ($loans as $loan) {
                    $monthly = (float) ($loan->monthly_amortization ?? 0);
                    $remaining = (float) ($loan->remaining_balance ?? $loan->amount ?? 0);
                    if ($monthly <= 0 || $remaining <= 0) {
                        continue;
                    }

                    $paymentAmount = min($monthly, $remaining);
                    if ($paymentAmount <= 0) {
                        continue;
                    }

                    $nextPayId = ((int) LoanPayment::query()->max('id')) + 1;
                    LoanPayment::query()->create([
                        'id' => $nextPayId,
                        'loan_request_id' => $loan->id,
                        'employee_id' => $employeeId,
                        'payroll_run_id' => $run->id,
                        'amount' => $paymentAmount,
                        'payment_date' => $periodEnd->toDateString(),
                        'notes' => null,
                    ]);

                    $loan->total_paid = (float) ($loan->total_paid ?? 0) + $paymentAmount;
                    $loan->remaining_balance = max($remaining - $paymentAmount, 0);
                    $loan->loan_status = ((float) $loan->remaining_balance) <= 0 ? 'paid' : 'active';
                    $loan->save();
                }
            }
        });

        ActivityLogger::log('created', 'PayrollRun', $run->id, "Saved payroll run: {$run->name}");

        return redirect()->route('payroll.show', $run)->with('status', 'payroll-saved');
    }

    public function show(Request $request, PayrollRun $payrollRun)
    {
        $user = $request->user();
        if (!$user?->canManageBackoffice()) {
            abort(403);
        }

        $payrollRun->load(['items.employee.department', 'creator', 'approver', 'batch']);

        return view('payroll.show', [
            'run' => $payrollRun,
        ]);
    }

    public function approve(Request $request, PayrollRun $payrollRun): RedirectResponse
    {
        $user = $request->user();
        if (!$user?->canManageBackoffice()) {
            abort(403);
        }

        $payrollRun->status = 'approved';
        $payrollRun->approved_by = optional($user->employee)->id;
        $payrollRun->approved_at = now();
        $payrollRun->save();

        ActivityLogger::log('approved', 'PayrollRun', $payrollRun->id, "Approved payroll run: {$payrollRun->name}");

        return redirect()->route('payroll.show', $payrollRun)->with('status', 'payroll-approved');
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
