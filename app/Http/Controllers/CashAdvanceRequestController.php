<?php

namespace App\Http\Controllers;

use App\Models\CashAdvanceRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redirect;

class CashAdvanceRequestController extends Controller
{
    public function show(Request $request, int $id)
    {
        $user = $request->user();
        $employee = $user?->employee;

        if (!$employee) {
            abort(403);
        }

        $cashAdvance = CashAdvanceRequest::query()
            ->with(['employee.department', 'approver', 'releaser'])
            ->where('id', $id)
            ->firstOrFail();

        if (!$user->canManageBackoffice() && (int) $cashAdvance->employee_id !== (int) $employee->id) {
            abort(403);
        }

        return view('cash-advance-requests.show', [
            'cashAdvance' => $cashAdvance,
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
            'amount' => ['required', 'numeric', 'min:1'],
            'reason' => ['nullable', 'string'],
            'attachment' => ['nullable', 'file', 'max:5120', 'mimes:jpg,jpeg,png,pdf,doc,docx,xls,xlsx'],
        ]);

        $attachmentPath = null;
        if ($request->hasFile('attachment')) {
            $attachmentPath = $request->file('attachment')->store('approvals', 'public');
        }

        $nextId = ((int) CashAdvanceRequest::query()->max('id')) + 1;

        CashAdvanceRequest::query()->create([
            'id' => $nextId,
            'employee_id' => $employee->id,
            'amount' => $validated['amount'],
            'reason' => $validated['reason'] ?? null,
            'attachment_path' => $attachmentPath,
            'status' => 'pending',
            'approved_by' => null,
            'admin_notes' => null,
        ]);

        return Redirect::route('requests.index')->with('status', 'cash-advance-request-created');
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
            $r = CashAdvanceRequest::query()->lockForUpdate()->where('id', $id)->firstOrFail();
            $r->status = 'approved';
            $r->approved_by = optional($user->employee)->id;
            $r->approved_at = now();
            $r->admin_notes = $validated['admin_notes'] ?? null;
            $r->save();
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

        $r = CashAdvanceRequest::query()->where('id', $id)->firstOrFail();
        $r->status = 'rejected';
        $r->approved_by = optional($user->employee)->id;
        $r->admin_notes = $validated['admin_notes'] ?? null;
        $r->save();

        return Redirect::back();
    }

    public function release(Request $request, int $id): RedirectResponse
    {
        $user = $request->user();
        if (!$user?->canManageBackoffice()) {
            abort(403);
        }

        DB::transaction(function () use ($id, $user) {
            $r = CashAdvanceRequest::query()->lockForUpdate()->where('id', $id)->firstOrFail();
            if ($r->status !== 'approved') {
                abort(422, 'Cash advance must be approved before it can be released.');
            }

            if ($r->released_at) {
                return;
            }

            if (!$r->approved_at) {
                $r->approved_at = now();
                $r->approved_by = $r->approved_by ?? optional($user->employee)->id;
            }

            $r->released_by = optional($user->employee)->id;
            $r->released_at = now();
            $r->save();
        });

        return Redirect::back();
    }
}
