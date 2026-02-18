<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\LeaveBalance;
use App\Services\ActivityLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;

class LeaveBalanceController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        if (!$user?->canManageBackoffice()) {
            abort(403);
        }

        $year = (int) $request->query('year', now()->year);
        $search = trim((string) $request->query('search', ''));

        $balances = LeaveBalance::query()
            ->with(['employee.department'])
            ->where('year', $year)
            ->when($search !== '', function ($q) use ($search) {
                $like = '%' . mb_strtolower($search) . '%';
                $q->whereHas('employee', function ($eq) use ($like) {
                    $eq->whereRaw('LOWER(first_name) LIKE ?', [$like])
                        ->orWhereRaw('LOWER(last_name) LIKE ?', [$like])
                        ->orWhereRaw('LOWER(employee_code) LIKE ?', [$like]);
                });
            })
            ->orderBy('employee_id')
            ->orderBy('leave_type')
            ->paginate(30)
            ->withQueryString();

        $employees = Employee::query()->orderBy('employee_code')->get();

        return view('leave-balances.index', [
            'balances' => $balances,
            'employees' => $employees,
            'year' => $year,
            'filters' => ['search' => $search],
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $user = $request->user();
        if (!$user?->canManageBackoffice()) {
            abort(403);
        }

        $validated = $request->validate([
            'employee_id' => ['required', 'integer', 'exists:employees,id'],
            'leave_type' => ['required', 'string', 'max:50'],
            'year' => ['required', 'integer', 'min:2020', 'max:2099'],
            'total_credits' => ['required', 'numeric', 'min:0'],
        ]);

        $existing = LeaveBalance::query()
            ->where('employee_id', $validated['employee_id'])
            ->where('leave_type', $validated['leave_type'])
            ->where('year', $validated['year'])
            ->first();

        if ($existing) {
            return Redirect::back()->withErrors(['leave_type' => 'A balance for this leave type and year already exists for this employee.'])->withInput();
        }

        $nextId = ((int) LeaveBalance::query()->max('id')) + 1;

        LeaveBalance::create([
            'id' => $nextId,
            'employee_id' => $validated['employee_id'],
            'leave_type' => $validated['leave_type'],
            'year' => $validated['year'],
            'total_credits' => $validated['total_credits'],
            'used' => 0,
            'remaining' => $validated['total_credits'],
        ]);

        ActivityLogger::log('created', 'LeaveBalance', $nextId, "Created leave balance for employee #{$validated['employee_id']}");

        return Redirect::route('leave-balances.index', ['year' => $validated['year']])->with('status', 'balance-created');
    }

    public function update(Request $request, LeaveBalance $leaveBalance): RedirectResponse
    {
        $user = $request->user();
        if (!$user?->canManageBackoffice()) {
            abort(403);
        }

        $validated = $request->validate([
            'total_credits' => ['required', 'numeric', 'min:0'],
        ]);

        $newTotal = (float) $validated['total_credits'];
        $used = (float) $leaveBalance->used;
        $remaining = max($newTotal - $used, 0);

        $leaveBalance->update([
            'total_credits' => $newTotal,
            'remaining' => $remaining,
        ]);

        ActivityLogger::log('updated', 'LeaveBalance', $leaveBalance->id, "Updated leave balance #{$leaveBalance->id}");

        return Redirect::route('leave-balances.index', ['year' => $leaveBalance->year])->with('status', 'balance-updated');
    }

    public function bulkCreate(Request $request): RedirectResponse
    {
        $user = $request->user();
        if (!$user?->canManageBackoffice()) {
            abort(403);
        }

        $validated = $request->validate([
            'leave_type' => ['required', 'string', 'max:50'],
            'year' => ['required', 'integer', 'min:2020', 'max:2099'],
            'total_credits' => ['required', 'numeric', 'min:0'],
        ]);

        $employees = Employee::query()
            ->whereHas('roles', fn ($q) => $q->where('name', 'employee'))
            ->where(function ($q) {
                $q->where('employment_status', 'active')
                    ->orWhereNull('employment_status');
            })
            ->get();

        $created = 0;
        foreach ($employees as $emp) {
            $exists = LeaveBalance::query()
                ->where('employee_id', $emp->id)
                ->where('leave_type', $validated['leave_type'])
                ->where('year', $validated['year'])
                ->exists();

            if (!$exists) {
                $nextId = ((int) LeaveBalance::query()->max('id')) + 1;
                LeaveBalance::create([
                    'id' => $nextId,
                    'employee_id' => $emp->id,
                    'leave_type' => $validated['leave_type'],
                    'year' => $validated['year'],
                    'total_credits' => $validated['total_credits'],
                    'used' => 0,
                    'remaining' => $validated['total_credits'],
                ]);
                $created++;
            }
        }

        ActivityLogger::log('created', 'LeaveBalance', null, "Bulk created {$created} leave balances for {$validated['leave_type']} {$validated['year']}");

        return Redirect::route('leave-balances.index', ['year' => $validated['year']])->with('status', "Created {$created} leave balances.");
    }
}
