<?php

namespace App\Http\Controllers;

use App\Models\AttendanceDailySummary;
use App\Models\LateRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Storage;

class LateRequestController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $employee = $user?->employee;

        if (!$employee) {
            abort(403);
        }

        $query = LateRequest::query()->with(['employee', 'approver']);

        if (!$user->isAdmin()) {
            $query->where('employee_id', $employee->id);
        }

        $status = trim((string) $request->query('status', ''));
        if ($status !== '' && in_array($status, ['pending', 'approved', 'rejected'], true)) {
            $query->where('status', $status);
        }

        $requests = $query->orderByDesc('id')->paginate(20)->withQueryString();

        return view('late-requests.index', [
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
            'date' => ['required', 'date'],
            'type' => ['required', 'string', 'in:late,undertime,missed_logs'],
            'reason' => ['nullable', 'string'],
            'image' => ['nullable', 'image', 'max:5120'],
        ]);

        $daily = AttendanceDailySummary::query()
            ->where('employee_code', $employee->employee_code)
            ->whereDate('summary_date', $validated['date'])
            ->first();

        if (!$daily) {
            return Redirect::back()->withErrors(['date' => 'No time tracking summary found for the selected date.'])->withInput();
        }

        $minutes = null;
        if ($validated['type'] === 'late') {
            $minutes = (int) $daily->late_in_minutes + (int) $daily->late_break_in_minutes;
            if ($minutes <= 0) {
                return Redirect::back()->withErrors(['type' => 'No late minutes found in time logs for the selected date.'])->withInput();
            }
        } elseif ($validated['type'] === 'undertime') {
            $minutes = (int) $daily->undertime_break_out_minutes;
            if ($minutes <= 0) {
                return Redirect::back()->withErrors(['type' => 'No undertime minutes found in time logs for the selected date.'])->withInput();
            }
        } elseif ($validated['type'] === 'missed_logs') {
            if ((int) $daily->missed_logs <= 0) {
                return Redirect::back()->withErrors(['type' => 'No missed logs found in time logs for the selected date.'])->withInput();
            }
        }

        $attachmentPath = null;
        if ($request->hasFile('image')) {
            $attachmentPath = $request->file('image')->store('approvals', 'public');
        }

        $nextId = ((int) LateRequest::query()->max('id')) + 1;

        LateRequest::query()->create([
            'id' => $nextId,
            'employee_id' => $employee->id,
            'date' => $validated['date'],
            'type' => $validated['type'],
            'minutes' => $minutes,
            'reason' => $validated['reason'] ?? null,
            'attachment_path' => $attachmentPath,
            'status' => 'pending',
            'approved_by' => null,
        ]);

        return Redirect::route('late-requests.index')->with('status', 'late-request-created');
    }

    public function approve(Request $request, int $id): RedirectResponse
    {
        $user = $request->user();
        if (!$user?->canManageBackoffice()) {
            abort(403);
        }

        $lr = LateRequest::query()->where('id', $id)->firstOrFail();
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

        $lr = LateRequest::query()->where('id', $id)->firstOrFail();
        $lr->status = 'rejected';
        $lr->approved_by = optional($user->employee)->id;
        $lr->save();

        return Redirect::back();
    }
}
