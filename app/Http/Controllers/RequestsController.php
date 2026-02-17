<?php

namespace App\Http\Controllers;

use App\Models\LeaveRequest;
use App\Models\LateRequest;
use App\Models\OvertimeRequest;
use App\Models\CashAdvanceRequest;
use App\Models\LoanRequest;
use Illuminate\Http\Request;

class RequestsController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $employee = $user?->employee;

        if (!$employee) {
            abort(403);
        }

        $status = trim((string) $request->query('status', ''));
        if ($status !== '' && !in_array($status, ['pending', 'approved', 'rejected'], true)) {
            $status = '';
        }

        $leaveQuery = LeaveRequest::query()->with(['employee', 'approver']);
        $otQuery = OvertimeRequest::query()->with(['employee', 'approver']);
        $lateQuery = LateRequest::query()->with(['employee', 'approver']);
        $cashAdvanceQuery = CashAdvanceRequest::query()->with(['employee', 'approver']);
        $loanQuery = LoanRequest::query()->with(['employee', 'approver']);

        $pendingInbox = null;
        if ($user->canManageBackoffice()) {
            $pendingAbsencesQuery = LeaveRequest::query()
                ->with(['employee', 'approver'])
                ->where('status', 'pending')
                ->orderByDesc('id');

            $pendingOvertimeQuery = OvertimeRequest::query()
                ->with(['employee', 'approver'])
                ->where('status', 'pending')
                ->orderByDesc('id');

            $pendingLateQuery = LateRequest::query()
                ->with(['employee', 'approver'])
                ->where('status', 'pending')
                ->orderByDesc('id');

            $pendingCashAdvanceQuery = CashAdvanceRequest::query()
                ->with(['employee', 'approver'])
                ->where('status', 'pending')
                ->orderByDesc('id');

            $pendingLoanQuery = LoanRequest::query()
                ->with(['employee', 'approver'])
                ->where('status', 'pending')
                ->orderByDesc('id');

            $pendingInbox = [
                'counts' => [
                    'absence' => (clone $pendingAbsencesQuery)->count(),
                    'overtime' => (clone $pendingOvertimeQuery)->count(),
                    'late' => (clone $pendingLateQuery)->count(),
                    'cash_advance' => (clone $pendingCashAdvanceQuery)->count(),
                    'loan' => (clone $pendingLoanQuery)->count(),
                ],
                'absence' => $pendingAbsencesQuery->limit(5)->get(),
                'overtime' => $pendingOvertimeQuery->limit(5)->get(),
                'late' => $pendingLateQuery->limit(5)->get(),
                'cash_advance' => $pendingCashAdvanceQuery->limit(5)->get(),
                'loan' => $pendingLoanQuery->limit(5)->get(),
            ];
        }

        if (!$user->isAdmin()) {
            $leaveQuery->where('employee_id', $employee->id);
            $otQuery->where('employee_id', $employee->id);
            $lateQuery->where('employee_id', $employee->id);
            $cashAdvanceQuery->where('employee_id', $employee->id);
            $loanQuery->where('employee_id', $employee->id);
        }

        if ($status !== '') {
            $leaveQuery->where('status', $status);
            $otQuery->where('status', $status);
            $lateQuery->where('status', $status);
            $cashAdvanceQuery->where('status', $status);
            $loanQuery->where('status', $status);
        }

        $leaveRequests = $leaveQuery->orderByDesc('id')
            ->paginate(10, ['*'], 'absence_page')
            ->withQueryString();

        $overtimeRequests = $otQuery->orderByDesc('id')
            ->paginate(10, ['*'], 'overtime_page')
            ->withQueryString();

        $lateRequests = $lateQuery->orderByDesc('id')
            ->paginate(10, ['*'], 'late_page')
            ->withQueryString();

        $cashAdvanceRequests = $cashAdvanceQuery->orderByDesc('id')
            ->paginate(10, ['*'], 'cash_advance_page')
            ->withQueryString();

        $loanRequests = $loanQuery->orderByDesc('id')
            ->paginate(10, ['*'], 'loan_page')
            ->withQueryString();

        return view('requests.index', [
            'filters' => [
                'status' => $status,
            ],
            'pendingInbox' => $pendingInbox,
            'leaveRequests' => $leaveRequests,
            'overtimeRequests' => $overtimeRequests,
            'lateRequests' => $lateRequests,
            'cashAdvanceRequests' => $cashAdvanceRequests,
            'loanRequests' => $loanRequests,
        ]);
    }
}
