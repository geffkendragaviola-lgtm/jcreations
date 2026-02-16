<?php

namespace App\Http\Controllers;

use App\Models\LeaveRequest;
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

        return view('approvals.index', [
            'pendingOvertime' => $pendingOvertime,
            'pendingAbsences' => $pendingAbsences,
        ]);
    }
}
