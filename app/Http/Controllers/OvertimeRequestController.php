<?php

namespace App\Http\Controllers;

use App\Models\AttendanceDailySummary;
use App\Models\Employee;
use App\Models\EmployeeScheduleOverride;
use App\Models\OvertimeRequest;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Storage;

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
            'hours' => ['nullable', 'numeric', 'min:0.25'],
            'description' => ['nullable', 'string'],
            'reason' => ['nullable', 'string'],
            'attachment' => ['nullable', 'file', 'max:5120', 'mimes:jpg,jpeg,png,pdf,doc,docx,xls,xlsx'],
        ]);

        $daily = AttendanceDailySummary::query()
            ->where('employee_code', $employee->employee_code)
            ->whereDate('summary_date', $validated['date'])
            ->first();

        $computedMinutes = null;
        $hours = null;
        if ($daily) {
            $computedMinutes = (int) $daily->ot_minutes;
            $hours = round($computedMinutes / 60, 2);
            if ($hours <= 0) {
                $hours = null;
            }
        }

        if ($hours === null) {
            $hours = isset($validated['hours']) ? (float) $validated['hours'] : null;
        }

        if ($hours === null || $hours <= 0) {
            return Redirect::back()->withErrors(['hours' => 'Please enter OT hours.'])->withInput();
        }

        $attachmentPath = null;
        if ($request->hasFile('attachment')) {
            $attachmentPath = $request->file('attachment')->store('approvals', 'public');
        }

        $nextId = ((int) OvertimeRequest::query()->max('id')) + 1;

        OvertimeRequest::query()->create([
            'id' => $nextId,
            'employee_id' => $employee->id,
            'date' => $validated['date'],
            'hours' => $hours,
            'description' => $validated['description'] ?? null,
            'attachment_path' => $attachmentPath,
            'computed_minutes' => $computedMinutes,
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

        $validated = $request->validate([
            'admin_notes' => ['nullable', 'string'],
        ]);

        $ot = OvertimeRequest::query()->where('id', $id)->firstOrFail();
        $ot->status = 'approved';
        $ot->approved_by = optional($user->employee)->id;
        $ot->admin_notes = $validated['admin_notes'] ?? null;
        $ot->save();

        $employeeId = (int) $ot->employee_id;
        $workDate = $ot->date ? Carbon::parse($ot->date)->toDateString() : null;
        $hours = $ot->hours !== null ? (float) $ot->hours : 0.0;

        if ($workDate && $hours > 0) {
            $employee = Employee::query()->with(['department', 'schedules'])->where('id', $employeeId)->first();

            $existing = EmployeeScheduleOverride::query()
                ->where('employee_id', $employeeId)
                ->whereDate('work_date', $workDate)
                ->first();

            $baselineStart = $employee?->department?->business_hours_start;
            $baselineEnd = $employee?->department?->business_hours_end;

            if ($employee) {
                $dow = Carbon::parse($workDate)->format('l');
                $weekly = $employee->schedules->firstWhere('day_of_week', $dow);
                if ($weekly) {
                    $baselineStart = $weekly->start_time;
                    $baselineEnd = $weekly->end_time;
                }
            }

            $startTime = $existing?->start_time ?? $baselineStart;
            $endTime = $existing?->end_time ?? $baselineEnd;

            $startTime = is_string($startTime) ? substr($startTime, 0, 5) : null;
            $endTime = is_string($endTime) ? substr($endTime, 0, 5) : null;

            if ($endTime !== null && preg_match('/^\d{2}:\d{2}$/', $endTime)) {
                $base = Carbon::createFromFormat('Y-m-d H:i', $workDate . ' ' . $endTime);
                $newEnd = $base->copy()->addMinutes((int) round($hours * 60));
                $endTime = $newEnd->format('H:i');
            }

            if ($existing) {
                $existing->is_working = true;
                $existing->start_time = $startTime;
                $existing->end_time = $endTime;
                $existing->save();
            } else {
                $nextId = ((int) EmployeeScheduleOverride::query()->max('id')) + 1;
                EmployeeScheduleOverride::query()->create([
                    'id' => $nextId,
                    'employee_id' => $employeeId,
                    'work_date' => $workDate,
                    'is_working' => true,
                    'start_time' => $startTime,
                    'end_time' => $endTime,
                ]);
            }
        }

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

        $ot = OvertimeRequest::query()->where('id', $id)->firstOrFail();
        $ot->status = 'rejected';
        $ot->approved_by = optional($user->employee)->id;
        $ot->admin_notes = $validated['admin_notes'] ?? null;
        $ot->save();

        return Redirect::back();
    }
}
