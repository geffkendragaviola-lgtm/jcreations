<?php

namespace App\Http\Controllers;

use App\Models\TimeAdjustmentRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Storage;

class TimeAdjustmentRequestController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $employee = $user?->employee;

        if (!$employee) {
            abort(403);
        }

        $query = TimeAdjustmentRequest::query()->with(['employee', 'approver']);

        if (!$user->isAdmin()) {
            $query->where('employee_id', $employee->id);
        }

        $status = trim((string) $request->query('status', ''));
        if ($status !== '' && in_array($status, ['pending', 'approved', 'rejected'], true)) {
            $query->where('status', $status);
        }

        $requests = $query->orderByDesc('id')->paginate(20)->withQueryString();

        return view('time-adjustment-requests.index', [
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
            'adjustment_type' => ['required', 'string', 'in:planned_late,planned_early_out,half_day,official_business,emergency_short_hours'],
            'minutes' => ['nullable', 'integer', 'min:0'],
            'reason' => ['nullable', 'string'],
            'attachment' => ['nullable', 'file', 'max:5120', 'mimes:jpg,jpeg,png,pdf,doc,docx,xls,xlsx'],
        ]);

        $attachmentPath = null;
        if ($request->hasFile('attachment')) {
            $attachmentPath = $request->file('attachment')->store('approvals', 'public');
        }

        $nextId = ((int) TimeAdjustmentRequest::query()->max('id')) + 1;

        TimeAdjustmentRequest::query()->create([
            'id' => $nextId,
            'employee_id' => $employee->id,
            'date' => $validated['date'],
            'adjustment_type' => $validated['adjustment_type'],
            'minutes' => $validated['minutes'] ?? null,
            'reason' => $validated['reason'] ?? null,
            'attachment_path' => $attachmentPath,
            'status' => 'pending',
            'approved_by' => null,
            'admin_notes' => null,
        ]);

        return Redirect::route('time-adjustment-requests.index')->with('status', 'time-adjustment-request-created');
    }

    public function approve(Request $request, int $id): RedirectResponse
    {
        $user = $request->user();
        if (!$user?->canManageBackoffice()) {
            abort(403);
        }

        $validated = $request->validate([
            'admin_notes' => ['nullable', 'string'],
        ]);

        $tar = TimeAdjustmentRequest::query()->where('id', $id)->firstOrFail();
        $tar->status = 'approved';
        $tar->approved_by = optional($user->employee)->id;
        $tar->admin_notes = $validated['admin_notes'] ?? null;
        $tar->save();

        return Redirect::back();
    }

    public function reject(Request $request, int $id): RedirectResponse
    {
        $user = $request->user();
        if (!$user?->canManageBackoffice()) {
            abort(403);
        }

        $validated = $request->validate([
            'admin_notes' => ['nullable', 'string'],
        ]);

        $tar = TimeAdjustmentRequest::query()->where('id', $id)->firstOrFail();
        $tar->status = 'rejected';
        $tar->approved_by = optional($user->employee)->id;
        $tar->admin_notes = $validated['admin_notes'] ?? null;
        $tar->save();

        return Redirect::back();
    }
}
