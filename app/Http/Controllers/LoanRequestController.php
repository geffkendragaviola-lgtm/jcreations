<?php

namespace App\Http\Controllers;

use App\Models\LoanPayment;
use App\Models\LoanRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redirect;

class LoanRequestController extends Controller
{
    public function show(Request $request, int $id)
    {
        $user = $request->user();
        $employee = $user?->employee;

        if (!$employee) {
            abort(403);
        }

        $loan = LoanRequest::query()
            ->with(['employee.department', 'approver', 'releaser', 'payments' => fn ($q) => $q->orderByDesc('payment_date')])
            ->where('id', $id)
            ->firstOrFail();

        if (!$user->canManageBackoffice() && (int) $loan->employee_id !== (int) $employee->id) {
            abort(403);
        }

        return view('loan-requests.show', [
            'loan' => $loan,
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

        DB::transaction(function () use ($id, $user, $validated) {
            $r = LoanRequest::query()->lockForUpdate()->where('id', $id)->firstOrFail();
            $r->status = 'approved';
            $r->loan_status = 'active';
            $r->approved_by = optional($user->employee)->id;
            $r->approved_at = now();
            $r->admin_notes = $validated['admin_notes'] ?? null;

            if (!is_null($r->term_months) && (int) $r->term_months > 0) {
                $r->monthly_amortization = round(((float) $r->amount) / (int) $r->term_months, 2);
            }

            if (is_null($r->total_paid)) {
                $r->total_paid = 0;
            }
            if (is_null($r->remaining_balance)) {
                $r->remaining_balance = (float) $r->amount;
            }

            $r->save();
        });

        return Redirect::back();
    }

    public function release(Request $request, int $id): RedirectResponse
    {
        $user = $request->user();
        if (!$user?->canManageBackoffice()) {
            abort(403);
        }

        DB::transaction(function () use ($id, $user) {
            $r = LoanRequest::query()->lockForUpdate()->where('id', $id)->firstOrFail();
            if ($r->status !== 'approved') {
                abort(422, 'Loan must be approved before it can be released.');
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
        $r->loan_status = 'rejected';
        $r->approved_by = optional($user->employee)->id;
        $r->admin_notes = $validated['admin_notes'] ?? null;
        $r->save();

        return Redirect::back();
    }

    public function addPayment(Request $request, int $id): RedirectResponse
    {
        $user = $request->user();
        if (!$user?->canManageBackoffice()) {
            abort(403);
        }

        $validated = $request->validate([
            'amount' => ['required', 'numeric', 'min:0.01'],
            'payment_date' => ['required', 'date'],
            'notes' => ['nullable', 'string'],
        ]);

        DB::transaction(function () use ($id, $validated, $user) {
            $r = LoanRequest::query()->lockForUpdate()->where('id', $id)->firstOrFail();

            if (!in_array($r->loan_status, ['active', 'pending'], true) || $r->status !== 'approved') {
                abort(422, 'Loan is not active.');
            }

            $remainingBefore = (float) ($r->remaining_balance ?? $r->amount);
            $amount = (float) $validated['amount'];
            if ($amount > $remainingBefore) {
                abort(422, 'Payment amount exceeds remaining balance.');
            }

            $nextId = ((int) LoanPayment::query()->max('id')) + 1;
            LoanPayment::query()->create([
                'id' => $nextId,
                'loan_request_id' => $r->id,
                'employee_id' => $r->employee_id,
                'payroll_run_id' => null,
                'amount' => $amount,
                'payment_date' => $validated['payment_date'],
                'notes' => $validated['notes'] ?? null,
            ]);

            $newTotalPaid = (float) ($r->total_paid ?? 0) + $amount;
            $newRemaining = $remainingBefore - $amount;

            $r->total_paid = $newTotalPaid;
            $r->remaining_balance = $newRemaining;
            $r->loan_status = $newRemaining <= 0 ? 'paid' : 'active';
            $r->approved_by = $r->approved_by ?? optional($user->employee)->id;
            $r->save();
        });

        return Redirect::back()->with('status', 'loan-payment-added');
    }
}
