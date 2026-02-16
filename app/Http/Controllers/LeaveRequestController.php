<?php

namespace App\Http\Controllers;

use App\Models\LeaveRequest;
use App\Models\AttendanceDailySummary;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Storage;

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
            'day_type' => ['required', 'string', 'in:full_day,half_day'],
            'description' => ['nullable', 'string'],
            'image' => ['nullable', 'image', 'max:5120'],
        ]);

        $leaveType = strtolower(trim((string) $validated['leave_type']));
        if ($leaveType !== 'leave without pay') {
            return Redirect::back()->withErrors(['leave_type' => 'Time off type must be leave without pay.'])->withInput();
        }

        if ($validated['day_type'] === 'half_day' && $validated['start_date'] !== $validated['end_date']) {
            return Redirect::back()->withErrors(['end_date' => 'Half-day time off must have the same start and end date.'])->withInput();
        }

        $days = (\Carbon\Carbon::parse($validated['start_date'])->startOfDay())
            ->diffInDays(\Carbon\Carbon::parse($validated['end_date'])->startOfDay()) + 1;

        $durationDays = $validated['day_type'] === 'half_day' ? 0.5 : (float) $days;

        $startC = \Carbon\Carbon::parse($validated['start_date'])->startOfDay();
        $endC = \Carbon\Carbon::parse($validated['end_date'])->startOfDay();
        $rangeDates = [];
        for ($d = $startC->copy(); $d->lte($endC); $d->addDay()) {
            $rangeDates[] = $d->toDateString();
        }

        $daily = AttendanceDailySummary::query()
            ->where('employee_code', $employee->employee_code)
            ->whereDate('summary_date', '>=', $validated['start_date'])
            ->whereDate('summary_date', '<=', $validated['end_date'])
            ->get()
            ->keyBy(fn ($r) => optional($r->summary_date)->format('Y-m-d'));

        foreach ($rangeDates as $dateStr) {
            $row = $daily->get($dateStr);
            if (!$row) {
                return Redirect::back()->withErrors(['start_date' => 'No time tracking summary found for one or more selected dates.'])->withInput();
            }
            if ((string) $row->status !== 'ABSENT') {
                return Redirect::back()->withErrors(['start_date' => 'Selected dates must be marked ABSENT in time tracking logs.'])->withInput();
            }
        }

        $attachmentPath = null;
        if ($request->hasFile('image')) {
            $attachmentPath = $request->file('image')->store('approvals', 'public');
        }

        $nextId = ((int) LeaveRequest::query()->max('id')) + 1;

        LeaveRequest::query()->create([
            'id' => $nextId,
            'employee_id' => $employee->id,
            'leave_type' => 'leave without pay',
            'start_date' => $validated['start_date'],
            'end_date' => $validated['end_date'],
            'day_type' => $validated['day_type'],
            'duration_days' => $durationDays,
            'description' => $validated['description'] ?? null,
            'attachment_path' => $attachmentPath,
            'status' => 'pending',
            'approved_by' => null,
        ]);

        return Redirect::route('leave-requests.index')->with('status', 'leave-request-created');
    }

    public function approve(Request $request, int $id): RedirectResponse
    {
        $user = $request->user();
        if (!$user?->canManageBackoffice()) {
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
        if (!$user?->canManageBackoffice()) {
            abort(403);
        }

        $lr = LeaveRequest::query()->where('id', $id)->firstOrFail();
        $lr->status = 'rejected';
        $lr->approved_by = optional($user->employee)->id;
        $lr->save();

        return Redirect::back();
    }
}
