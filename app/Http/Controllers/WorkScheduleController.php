<?php

namespace App\Http\Controllers;

use App\Models\Department;
use App\Models\Employee;
use App\Models\EmployeeSchedule;
use App\Models\EmployeeScheduleOverride;
use App\Models\OvertimeRequest;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;

class WorkScheduleController extends Controller
{
    private array $days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];

    public function index(Request $request)
    {
        $user = $request->user();
        if (!$user?->canManageBackoffice()) {
            abort(403);
        }

        $search = trim((string) $request->query('search', ''));
        $departmentId = $request->query('department_id');

        $employees = Employee::query()
            ->with(['department', 'schedules'])
            ->when($departmentId !== null && $departmentId !== '', function ($query) use ($departmentId) {
                $query->where('department_id', $departmentId);
            })
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('employee_code', 'like', '%' . $search . '%')
                        ->orWhere('first_name', 'like', '%' . $search . '%')
                        ->orWhere('last_name', 'like', '%' . $search . '%');
                });
            })
            ->orderBy('employee_code')
            ->paginate(25)
            ->withQueryString();

        $departments = Department::query()->orderBy('name')->get();

        return view('work-schedules.index', [
            'employees' => $employees,
            'departments' => $departments,
            'days' => $this->days,
        ]);
    }

    public function update(Request $request, Employee $employee): RedirectResponse
    {
        $user = $request->user();
        if (!$user?->canManageBackoffice()) {
            abort(403);
        }

        $validated = $request->validate([
            'redirect_to' => ['nullable', 'string'],
            'schedule' => ['array'],
        ]);

        $schedule = (array) ($validated['schedule'] ?? []);

        foreach ($this->days as $day) {
            $dayData = (array) ($schedule[$day] ?? []);
            $start = $dayData['start'] ?? null;
            $end = $dayData['end'] ?? null;

            $start = is_string($start) ? trim($start) : null;
            $end = is_string($end) ? trim($end) : null;

            if ($start === '') {
                $start = null;
            }
            if ($end === '') {
                $end = null;
            }

            if ($start !== null && !preg_match('/^\d{2}:\d{2}$/', $start)) {
                return Redirect::back()->withErrors(['schedule' => 'Invalid start time format.'])->withInput();
            }
            if ($end !== null && !preg_match('/^\d{2}:\d{2}$/', $end)) {
                return Redirect::back()->withErrors(['schedule' => 'Invalid end time format.'])->withInput();
            }

            $existing = EmployeeSchedule::query()
                ->where('employee_id', $employee->id)
                ->where('day_of_week', $day)
                ->first();

            if ($start === null || $end === null) {
                if ($existing) {
                    $existing->delete();
                }
                continue;
            }

            if ($existing) {
                $existing->start_time = $start;
                $existing->end_time = $end;
                $existing->save();
                continue;
            }

            $nextId = ((int) EmployeeSchedule::query()->max('id')) + 1;

            EmployeeSchedule::query()->create([
                'id' => $nextId,
                'employee_id' => $employee->id,
                'day_of_week' => $day,
                'start_time' => $start,
                'end_time' => $end,
            ]);
        }

        $redirectTo = $request->input('redirect_to');
        if (is_string($redirectTo) && $redirectTo !== '') {
            if (str_starts_with($redirectTo, '/')) {
                return redirect($redirectTo)->with('status', 'schedule-updated');
            }

            $appUrl = rtrim((string) config('app.url'), '/');
            if ($appUrl !== '' && str_starts_with($redirectTo, $appUrl)) {
                return redirect()->to($redirectTo)->with('status', 'schedule-updated');
            }
        }

        return Redirect::route('work-schedules.index')->with('status', 'schedule-updated');
    }

    public function calendar(Request $request, Employee $employee)
    {
        $user = $request->user();
        if (!$user?->canManageBackoffice()) {
            abort(403);
        }

        $month = trim((string) $request->query('month', ''));
        $monthC = $month !== '' ? Carbon::createFromFormat('Y-m', $month)->startOfMonth() : Carbon::today()->startOfMonth();
        $start = $monthC->copy();
        $end = $monthC->copy()->endOfMonth();

        $employee->load(['department', 'schedules', 'scheduleOverrides']);

        $scheduleMap = $employee->schedules->keyBy('day_of_week');
        $overrideMap = $employee->scheduleOverrides
            ->filter(fn ($o) => $o->work_date && $o->work_date->betweenIncluded($start, $end))
            ->keyBy(fn ($o) => $o->work_date->format('Y-m-d'));

        $approvedOt = OvertimeRequest::query()
            ->where('employee_id', $employee->id)
            ->where('status', 'approved')
            ->whereBetween('date', [$start->toDateString(), $end->toDateString()])
            ->orderBy('date')
            ->get();

        $otByDate = $approvedOt->groupBy(fn ($ot) => $ot->date?->format('Y-m-d'));

        $defaultStart = $employee->department?->business_hours_start;
        $defaultEnd = $employee->department?->business_hours_end;

        $gridStart = $start->copy()->startOfWeek(Carbon::SUNDAY);
        $gridEnd = $end->copy()->endOfWeek(Carbon::SATURDAY);

        $days = [];
        for ($d = $gridStart->copy(); $d->lte($gridEnd); $d->addDay()) {
            $key = $d->format('Y-m-d');
            $override = $overrideMap->get($key);

            $dow = $d->format('l');
            $weekly = $scheduleMap->get($dow);

            $baselineWorking = in_array($dow, ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'], true);
            $baselineStart = $baselineWorking ? $defaultStart : null;
            $baselineEnd = $baselineWorking ? $defaultEnd : null;

            if (in_array($dow, ['Saturday', 'Sunday'], true)) {
                $baselineWorking = false;
                $baselineStart = null;
                $baselineEnd = null;
            }

            if ($weekly) {
                $baselineWorking = true;
                $baselineStart = $weekly->start_time;
                $baselineEnd = $weekly->end_time;
            }

            $effectiveWorking = $override ? (bool) $override->is_working : $baselineWorking;
            $effectiveStart = $override ? $override->start_time : $baselineStart;
            $effectiveEnd = $override ? $override->end_time : $baselineEnd;

            if (!$effectiveWorking) {
                $effectiveStart = null;
                $effectiveEnd = null;
            }

            $baselineStart = is_string($baselineStart) ? substr($baselineStart, 0, 5) : null;
            $baselineEnd = is_string($baselineEnd) ? substr($baselineEnd, 0, 5) : null;
            $effectiveStart = is_string($effectiveStart) ? substr($effectiveStart, 0, 5) : null;
            $effectiveEnd = is_string($effectiveEnd) ? substr($effectiveEnd, 0, 5) : null;

            $otForDay = $otByDate->get($key, collect());
            $otPills = $otForDay
                ->filter(fn ($ot) => $ot && $ot->hours !== null)
                ->map(fn ($ot) => [
                    'hours' => (string) $ot->hours,
                    'label' => 'OT ' . number_format((float) $ot->hours, 2) . 'h',
                    'reason' => $ot->reason,
                ])
                ->values()
                ->all();

            $days[] = [
                'date' => $d->copy(),
                'key' => $key,
                'in_month' => $d->month === $monthC->month,
                'dow' => $dow,
                'has_override' => $override !== null,
                'working' => $effectiveWorking,
                'start' => $effectiveStart,
                'end' => $effectiveEnd,
                'ot' => $otPills,
                'baseline_working' => $baselineWorking,
                'baseline_start' => $baselineStart,
                'baseline_end' => $baselineEnd,
            ];
        }

        return view('work-schedules.calendar', [
            'employee' => $employee,
            'month' => $monthC,
            'days' => $days,
        ]);
    }

    public function updateOverride(Request $request, Employee $employee): RedirectResponse
    {
        $user = $request->user();
        if (!$user?->canManageBackoffice()) {
            abort(403);
        }

        $validated = $request->validate([
            'redirect_to' => ['nullable', 'string'],
            'work_date' => ['required', 'date'],
            'is_working' => ['nullable'],
            'start_time' => ['nullable', 'regex:/^\d{2}:\d{2}(:\d{2})?$/'],
            'end_time' => ['nullable', 'regex:/^\d{2}:\d{2}(:\d{2})?$/'],
        ]);

        $workDate = Carbon::parse($validated['work_date'])->toDateString();
        $isWorking = (string) ($validated['is_working'] ?? '') === '1';
        $startTime = $validated['start_time'] ?? null;
        $endTime = $validated['end_time'] ?? null;

        $startTime = is_string($startTime) ? substr($startTime, 0, 5) : null;
        $endTime = is_string($endTime) ? substr($endTime, 0, 5) : null;

        if (!$isWorking) {
            $startTime = null;
            $endTime = null;
        }

        $existing = EmployeeScheduleOverride::query()
            ->where('employee_id', $employee->id)
            ->whereDate('work_date', $workDate)
            ->first();

        if ($isWorking && ($startTime === null || $endTime === null)) {
            if ($existing) {
                $existing->delete();
            }
        } else {
            if ($existing) {
                $existing->is_working = $isWorking;
                $existing->start_time = $startTime;
                $existing->end_time = $endTime;
                $existing->save();
            } else {
                $nextId = ((int) EmployeeScheduleOverride::query()->max('id')) + 1;
                EmployeeScheduleOverride::query()->create([
                    'id' => $nextId,
                    'employee_id' => $employee->id,
                    'work_date' => $workDate,
                    'is_working' => $isWorking,
                    'start_time' => $startTime,
                    'end_time' => $endTime,
                ]);
            }
        }

        $redirectTo = $request->input('redirect_to');
        if (is_string($redirectTo) && $redirectTo !== '') {
            if (str_starts_with($redirectTo, '/')) {
                return redirect($redirectTo)->with('status', 'schedule-override-updated');
            }

            $appUrl = rtrim((string) config('app.url'), '/');
            if ($appUrl !== '' && str_starts_with($redirectTo, $appUrl)) {
                return redirect()->to($redirectTo)->with('status', 'schedule-override-updated');
            }
        }

        return Redirect::route('work-schedules.calendar', $employee)->with('status', 'schedule-override-updated');
    }
}
