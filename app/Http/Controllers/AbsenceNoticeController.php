<?php

namespace App\Http\Controllers;

use App\Models\AbsenceNotice;
use App\Models\AttendanceDailySummary;
use App\Models\Employee;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Storage;

class AbsenceNoticeController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $employee = $user?->employee;

        if (!$employee) {
            abort(403);
        }

        $query = AbsenceNotice::query()->with(['employee', 'approver']);

        if (!$user->isAdmin()) {
            $query->where('employee_id', $employee->id);
        }

        $status = trim((string) $request->query('status', ''));
        if ($status !== '' && in_array($status, ['pending', 'approved', 'rejected'], true)) {
            $query->where('status', $status);
        }

        $notices = $query->orderByDesc('id')->paginate(20)->withQueryString();

        return view('absence-notices.index', [
            'notices' => $notices,
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
            'reason' => ['nullable', 'string'],
            'attachment' => ['nullable', 'file', 'max:5120', 'mimes:jpg,jpeg,png,pdf,doc,docx,xls,xlsx'],
        ]);

        $daily = AttendanceDailySummary::query()
            ->where('employee_code', $employee->employee_code)
            ->whereDate('summary_date', $validated['date'])
            ->first();

        if (!$daily) {
            return Redirect::back()->withErrors(['date' => 'No time tracking summary found for the selected date.'])->withInput();
        }

        $statusStr = strtolower(trim((string) ($daily->status ?? '')));
        if ($statusStr === '' || !str_contains($statusStr, 'absent')) {
            return Redirect::back()->withErrors(['date' => 'The selected date is not marked as ABSENT in time tracking.'])->withInput();
        }

        $attachmentPath = null;
        if ($request->hasFile('attachment')) {
            $attachmentPath = $request->file('attachment')->store('approvals', 'public');
        }

        DB::transaction(function () use ($employee, $validated, $attachmentPath) {
            $existing = AbsenceNotice::query()
                ->where('employee_id', $employee->id)
                ->whereDate('date', $validated['date'])
                ->orderByDesc('id')
                ->lockForUpdate()
                ->first();

            if ($existing) {
                if (in_array((string) $existing->status, ['approved', 'rejected'], true)) {
                    return;
                }

                $existing->reason = $validated['reason'] ?? null;
                if ($attachmentPath !== null) {
                    $existing->attachment_path = $attachmentPath;
                }
                $existing->status = 'pending';
                $existing->approved_by = null;
                $existing->save();

                return;
            }

            $nextId = ((int) AbsenceNotice::query()->max('id')) + 1;
            AbsenceNotice::query()->create([
                'id' => $nextId,
                'employee_id' => $employee->id,
                'date' => $validated['date'],
                'detected_from_summary' => false,
                'reason' => $validated['reason'] ?? null,
                'attachment_path' => $attachmentPath,
                'status' => 'pending',
                'approved_by' => null,
            ]);
        });

        $existingApprovedOrRejected = AbsenceNotice::query()
            ->where('employee_id', $employee->id)
            ->whereDate('date', $validated['date'])
            ->whereIn('status', ['approved', 'rejected'])
            ->exists();

        if ($existingApprovedOrRejected) {
            if ($attachmentPath !== null) {
                Storage::disk('public')->delete($attachmentPath);
            }

            return Redirect::back()
                ->withErrors(['date' => 'This notice has already been reviewed and cannot be refiled.'])
                ->withInput();
        }

        return Redirect::route('absence-notices.index')->with('status', 'absence-notice-submitted');
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

        DB::transaction(function () use ($id, $user, $validated) {
            $n = AbsenceNotice::query()->where('id', $id)->lockForUpdate()->firstOrFail();
            $n->status = 'approved';
            $n->approved_by = optional($user->employee)->id;
            $n->admin_notes = $validated['admin_notes'] ?? null;
            $n->save();
        });

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

        DB::transaction(function () use ($id, $user, $validated) {
            $n = AbsenceNotice::query()->where('id', $id)->lockForUpdate()->firstOrFail();
            $n->status = 'rejected';
            $n->approved_by = optional($user->employee)->id;
            $n->admin_notes = $validated['admin_notes'] ?? null;
            $n->save();
        });

        return Redirect::back();
    }
}
