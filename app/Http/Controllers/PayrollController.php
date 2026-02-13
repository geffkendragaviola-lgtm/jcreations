<?php

namespace App\Http\Controllers;

use App\Models\AttendanceDailySummary;
use App\Models\Employee;
use App\Models\LeaveRequest;
use App\Models\OvertimeRequest;
use Carbon\Carbon;
use Illuminate\Http\Request;

class PayrollController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        if (!$user?->isAdmin()) {
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

        [$start, $end] = $this->resolveRange($request, $mode);

        $rows = [];

        if ($start && $end) {
            $employees = Employee::query()->with('department')->orderBy('employee_code')->get();

            $daily = AttendanceDailySummary::query()
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

            foreach ($employees as $emp) {
                $dailyRate = (float) ($emp->daily_rate ?? 0);
                $hourlyRate = $baseHoursPerDay > 0 ? ($dailyRate / $baseHoursPerDay) : 0;

                $empDaily = $daily->get($emp->employee_code, collect());

                $daysWorked = $empDaily->count();
                $lateHours = round(((int) $empDaily->sum(fn ($d) => (int) $d->late_in_minutes + (int) $d->late_break_in_minutes)) / 60, 2);
                $undertimeHours = round(((int) $empDaily->sum('undertime_break_out_minutes')) / 60, 2);

                $otHours = round(((float) $approvedOt->get($emp->id, collect())->sum('hours')), 2);

                $absenceDays = $this->countApprovedLeaveDaysInRange($approvedLeave->get($emp->id, collect()), $start, $end);

                $gross = round($daysWorked * $dailyRate, 2);
                $lateDeduction = round($lateHours * $hourlyRate, 2);
                $undertimeDeduction = round($undertimeHours * $hourlyRate, 2);
                $absenceDeduction = round($absenceDays * $dailyRate, 2);

                $otPay = round($otHours * $hourlyRate * $otMultiplier, 2);

                $gov = $request->input('government_deduction.' . $emp->id);
                $gov = $gov !== null ? (float) $gov : (float) ($emp->government_deduction ?? 0);

                $net = round($gross + $otPay - $lateDeduction - $undertimeDeduction - $absenceDeduction - $gov, 2);

                $rows[] = [
                    'employee_id' => $emp->id,
                    'employee_code' => $emp->employee_code,
                    'employee_name' => $emp->full_name,
                    'department' => $emp->department?->name,
                    'daily_rate' => $dailyRate,
                    'hourly_rate' => round($hourlyRate, 2),
                    'days_worked' => $daysWorked,
                    'late_hours' => $lateHours,
                    'undertime_hours' => $undertimeHours,
                    'approved_ot_hours' => $otHours,
                    'approved_absence_days' => $absenceDays,
                    'gross_pay' => $gross,
                    'ot_pay' => $otPay,
                    'late_deduction' => $lateDeduction,
                    'undertime_deduction' => $undertimeDeduction,
                    'absence_deduction' => $absenceDeduction,
                    'government_deduction' => round($gov, 2),
                    'net_pay' => $net,
                ];
            }

            if ($request->isMethod('post') && (string) $request->input('save_government_deduction', '') === '1') {
                foreach ($rows as $r) {
                    Employee::query()->where('id', $r['employee_id'])->update([
                        'government_deduction' => $r['government_deduction'],
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

    private function countApprovedLeaveDaysInRange($leaveRequests, string $start, string $end): int
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

            $days += $from->diffInDays($to) + 1;
        }

        return $days;
    }
}
