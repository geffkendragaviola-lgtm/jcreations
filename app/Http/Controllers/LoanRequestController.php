<?php

namespace App\Http\Controllers;

use App\Models\LoanRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;

class LoanRequestController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        $user = $request->user();
        $employee = $user?->employee;

        if (!$employee) {
            abort(403);
        }

        $validated = $request->validate([
            'amount' => ['required', 'numeric', 'min:1'],
            'term_months' => ['nullable', 'integer', 'min:1'],
            'purpose' => ['nullable', 'string'],
            'attachment' => ['nullable', 'file', 'max:5120', 'mimes:jpg,jpeg,png,pdf,doc,docx,xls,xlsx'],
        ]);

        $attachmentPath = null;
        if ($request->hasFile('attachment')) {
            $attachmentPath = $request->file('attachment')->store('approvals', 'public');
        }

        $nextId = ((int) LoanRequest::query()->max('id')) + 1;

        LoanRequest::query()->create([
            'id' => $nextId,
            'employee_id' => $employee->id,
            'amount' => $validated['amount'],
            'term_months' => $validated['term_months'] ?? null,
            'purpose' => $validated['purpose'] ?? null,
            'attachment_path' => $attachmentPath,
            'status' => 'pending',
            'approved_by' => null,
            'admin_notes' => null,
        ]);

        return Redirect::route('requests.index')->with('status', 'loan-request-created');
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

        $r = LoanRequest::query()->where('id', $id)->firstOrFail();
        $r->status = 'approved';
        $r->approved_by = optional($user->employee)->id;
        $r->admin_notes = $validated['admin_notes'] ?? null;
        $r->save();

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

        $r = LoanRequest::query()->where('id', $id)->firstOrFail();
        $r->status = 'rejected';
        $r->approved_by = optional($user->employee)->id;
        $r->admin_notes = $validated['admin_notes'] ?? null;
        $r->save();

        return Redirect::back();
    }
}
