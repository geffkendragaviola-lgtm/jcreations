<?php

namespace App\Http\Controllers;

use App\Models\LeaveRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;

class LeaveRequestController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $employee = $user?->employee;

        if (!$employee) {
            abort(403);
        }

        $query = LeaveRequest::query()->with(['employee', 'approver']);

        if (!$user->isAdmin()) {
            $query->where('employee_id', $employee->id);
        }

        $status = trim((string) $request->query('status', ''));
        if ($status !== '' && in_array($status, ['pending', 'approved', 'rejected'], true)) {
            $query->where('status', $status);
        }

        $requests = $query->orderByDesc('id')->paginate(20)->withQueryString();

        return view('leave-requests.index', [
            'requests' => $requests,
            'filters' => [
                'status' => $status,
            ],
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $user = $request->user();
        $employee = $user?->employee;

        if (!$employee) {
            abort(403);
        }

        $validated = $request->validate([
            'leave_type' => ['required', 'string', 'max:50'],
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
        ]);

        $nextId = ((int) LeaveRequest::query()->max('id')) + 1;

        LeaveRequest::query()->create([
            'id' => $nextId,
            'employee_id' => $employee->id,
            'leave_type' => $validated['leave_type'],
            'start_date' => $validated['start_date'],
            'end_date' => $validated['end_date'],
            'status' => 'pending',
            'approved_by' => null,
        ]);

        return Redirect::route('leave-requests.index')->with('status', 'leave-request-created');
    }

    public function approve(Request $request, int $id): RedirectResponse
    {
        $user = $request->user();
        if (!$user?->isAdmin()) {
            abort(403);
        }

        $lr = LeaveRequest::query()->where('id', $id)->firstOrFail();
        $lr->status = 'approved';
        $lr->approved_by = optional($user->employee)->id;
        $lr->save();

        return Redirect::back();
    }

    public function reject(Request $request, int $id): RedirectResponse
    {
        $user = $request->user();
        if (!$user?->isAdmin()) {
            abort(403);
        }

        $lr = LeaveRequest::query()->where('id', $id)->firstOrFail();
        $lr->status = 'rejected';
        $lr->approved_by = optional($user->employee)->id;
        $lr->save();

        return Redirect::back();
    }
}
