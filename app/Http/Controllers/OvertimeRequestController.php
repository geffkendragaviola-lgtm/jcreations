<?php

namespace App\Http\Controllers;

use App\Models\OvertimeRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;

class OvertimeRequestController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $employee = $user?->employee;

        if (!$employee) {
            abort(403);
        }

        $query = OvertimeRequest::query()->with(['employee', 'approver']);

        if (!$user->isAdmin()) {
            $query->where('employee_id', $employee->id);
        }

        $status = trim((string) $request->query('status', ''));
        if ($status !== '' && in_array($status, ['pending', 'approved', 'rejected'], true)) {
            $query->where('status', $status);
        }

        $requests = $query->orderByDesc('id')->paginate(20)->withQueryString();

        return view('overtime-requests.index', [
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
            'hours' => ['required', 'numeric', 'min:0.01', 'max:24'],
            'reason' => ['nullable', 'string'],
        ]);

        $nextId = ((int) OvertimeRequest::query()->max('id')) + 1;

        OvertimeRequest::query()->create([
            'id' => $nextId,
            'employee_id' => $employee->id,
            'date' => $validated['date'],
            'hours' => $validated['hours'],
            'reason' => $validated['reason'] ?? null,
            'status' => 'pending',
            'approved_by' => null,
        ]);

        return Redirect::route('overtime-requests.index')->with('status', 'overtime-request-created');
    }

    public function approve(Request $request, int $id): RedirectResponse
    {
        $user = $request->user();
        if (!$user?->canManageBackoffice()) {
            abort(403);
        }

        $ot = OvertimeRequest::query()->where('id', $id)->firstOrFail();
        $ot->status = 'approved';
        $ot->approved_by = optional($user->employee)->id;
        $ot->save();

        return Redirect::back();
    }

    public function reject(Request $request, int $id): RedirectResponse
    {
        $user = $request->user();
        if (!$user?->canManageBackoffice()) {
            abort(403);
        }

        $ot = OvertimeRequest::query()->where('id', $id)->firstOrFail();
        $ot->status = 'rejected';
        $ot->approved_by = optional($user->employee)->id;
        $ot->save();

        return Redirect::back();
    }
}
