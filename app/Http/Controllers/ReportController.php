<?php

namespace App\Http\Controllers;

use App\Models\AttendanceDailySummary;
use App\Models\Employee;
use App\Models\LeaveRequest;
use App\Models\PayrollRun;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        if (!$user?->canManageBackoffice()) {
            abort(403);
        }

        return view('reports.index');
    }

    public function attendanceCsv(Request $request): StreamedResponse
    {
        $user = $request->user();
        if (!$user?->canManageBackoffice()) {
            abort(403);
        }

        $validated = $request->validate([
            'start' => ['required', 'date'],
            'end' => ['required', 'date', 'after_or_equal:start'],
        ]);

        $rows = AttendanceDailySummary::query()
            ->with(['employee.department'])
            ->whereDate('summary_date', '>=', $validated['start'])
            ->whereDate('summary_date', '<=', $validated['end'])
            ->orderBy('summary_date')
            ->orderBy('employee_code')
            ->get();

        return $this->streamCsv('attendance_report.csv', [
            'Employee Code', 'Employee Name', 'Department', 'Date',
            'Time In', 'Break Out', 'Break In', 'Time Out',
            'Late (min)', 'Undertime (min)', 'OT (min)', 'Total Hours', 'Status',
        ], $rows->map(function ($r) {
            return [
                $r->employee_code,
                $r->employee?->full_name ?? '',
                $r->employee?->department?->name ?? '',
                optional($r->summary_date)->format('Y-m-d'),
                $r->time_in,
                $r->break_out,
                $r->break_in,
                $r->time_out,
                (int) $r->late_in_minutes + (int) $r->late_break_in_minutes,
                (int) $r->undertime_break_out_minutes,
                (int) $r->ot_minutes,
                (float) $r->total_hours,
                $r->status,
            ];
        })->toArray());
    }

    public function leavesCsv(Request $request): StreamedResponse
    {
        $user = $request->user();
        if (!$user?->canManageBackoffice()) {
            abort(403);
        }

        $validated = $request->validate([
            'start' => ['required', 'date'],
            'end' => ['required', 'date', 'after_or_equal:start'],
        ]);

        $rows = LeaveRequest::query()
            ->with(['employee.department', 'approver'])
            ->whereDate('start_date', '>=', $validated['start'])
            ->whereDate('end_date', '<=', $validated['end'])
            ->orderByDesc('id')
            ->get();

        return $this->streamCsv('leaves_report.csv', [
            'ID', 'Employee', 'Department', 'Leave Type', 'Start', 'End',
            'Day Type', 'Duration', 'Status', 'Approved By', 'Description',
        ], $rows->map(function ($r) {
            return [
                $r->id,
                $r->employee?->full_name ?? '',
                $r->employee?->department?->name ?? '',
                $r->leave_type,
                optional($r->start_date)->format('Y-m-d'),
                optional($r->end_date)->format('Y-m-d'),
                $r->day_type,
                $r->duration_days,
                $r->status,
                $r->approver?->full_name ?? '',
                $r->description,
            ];
        })->toArray());
    }

    public function payrollCsv(Request $request): StreamedResponse
    {
        $user = $request->user();
        if (!$user?->canManageBackoffice()) {
            abort(403);
        }

        $validated = $request->validate([
            'payroll_run_id' => ['required', 'integer', 'exists:payroll_runs,id'],
        ]);

        $run = PayrollRun::query()->with(['items.employee.department'])->findOrFail($validated['payroll_run_id']);

        return $this->streamCsv('payroll_report.csv', [
            'Employee Code', 'Employee Name', 'Department',
            'Daily Rate', 'Regular Days', 'Rest Day Days', 'Paid Leave', 'Unpaid Leave', 'Absences',
            'Late Hrs', 'Undertime Hrs', 'OT Hrs',
            'Gross Pay', 'OT Pay', 'Late Ded', 'Undertime Ded', 'Absence Ded',
            'SSS', 'Pag-IBIG', 'PhilHealth', 'Tax', 'Cash Advance', 'Loan', 'Other',
            'Total Deductions', 'Net Pay',
        ], $run->items->map(function ($i) {
            return [
                $i->employee_code,
                $i->employee?->full_name ?? '',
                $i->employee?->department?->name ?? '',
                $i->daily_rate,
                $i->regular_worked_days,
                $i->rest_day_worked_days,
                $i->paid_leave_days,
                $i->unpaid_leave_days,
                $i->unpaid_absence_days,
                $i->late_hours,
                $i->undertime_hours,
                $i->approved_ot_hours,
                $i->gross_pay,
                $i->ot_pay,
                $i->late_deduction,
                $i->undertime_deduction,
                $i->absence_deduction,
                $i->sss_deduction,
                $i->pagibig_deduction,
                $i->philhealth_deduction,
                $i->tax_deduction,
                $i->cash_advance_deduction,
                $i->loan_deduction,
                $i->other_deductions,
                $i->total_deductions,
                $i->net_pay,
            ];
        })->toArray());
    }

    private function streamCsv(string $filename, array $headers, array $rows): StreamedResponse
    {
        return response()->streamDownload(function () use ($headers, $rows) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, $headers);
            foreach ($rows as $row) {
                fputcsv($handle, $row);
            }
            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv',
        ]);
    }
}
