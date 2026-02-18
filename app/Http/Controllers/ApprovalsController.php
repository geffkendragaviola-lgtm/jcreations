<?php

namespace App\Http\Controllers;

use App\Models\LeaveRequest;
use App\Models\LateRequest;
use App\Models\OvertimeRequest;
use Illuminate\Http\Request;

class ApprovalsController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        if (!$user?->canManageBackoffice()) {
            abort(403);
        }

        $pendingOvertime = OvertimeRequest::query()
            ->with(['employee', 'approver'])
            ->where('status', 'pending')
            ->orderByDesc('id')
            ->paginate(20, ['*'], 'overtime_page');

        $pendingAbsences = LeaveRequest::query()
            ->with(['employee', 'approver'])
            ->where('status', 'pending')
            ->orderByDesc('id')
            ->paginate(20, ['*'], 'absence_page');

        $pendingLate = LateRequest::query()
            ->with(['employee', 'approver'])
            ->where('status', 'pending')
            ->where(function ($q) {
                $q->whereNull('detected_from_summary')
                    ->orWhere('detected_from_summary', false)
                    ->orWhere(function ($q2) {
                        $q2->where('detected_from_summary', true)
                            ->where(function ($q3) {
                                $q3->whereNotNull('reason')->orWhereNotNull('attachment_path');
                            });
                    });
            })
            ->orderByDesc('id')
            ->paginate(20, ['*'], 'late_page');

        return view('approvals.index', [
            'pendingOvertime' => $pendingOvertime,
            'pendingAbsences' => $pendingAbsences,
            'pendingLate' => $pendingLate,
        ]);
    }
}
