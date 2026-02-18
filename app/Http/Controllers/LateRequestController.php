<?php

namespace App\Http\Controllers;

use App\Models\AttendanceDailySummary;
use App\Models\AttendancePeriodSummary;
use App\Models\Employee;
use App\Models\LateRequest;
use App\Services\Attendance\AttendanceMetricsCalculator;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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
            'attachment' => ['nullable', 'file', 'max:5120', 'mimes:jpg,jpeg,png,pdf,doc,docx,xls,xlsx'],
        ]);

        $daily = AttendanceDailySummary::query()
            ->where('employee_code', $employee->employee_code)
            ->whereDate('summary_date', $validated['date'])
            ->first();

        $minutes = null;
        if ($daily) {
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
        } else {
            $autoNotice = LateRequest::query()
                ->where('employee_id', $employee->id)
                ->whereDate('date', $validated['date'])
                ->where('type', $validated['type'])
                ->where('status', 'pending')
                ->where('detected_from_summary', true)
                ->orderByDesc('id')
                ->first();

            if (!$autoNotice) {
                return Redirect::back()->withErrors(['date' => 'No time tracking summary found for the selected date.'])->withInput();
            }
        }

        $attachmentPath = null;
        if ($request->hasFile('attachment')) {
            $attachmentPath = $request->file('attachment')->store('approvals', 'public');
        }

        DB::transaction(function () use ($employee, $validated, $minutes, $attachmentPath) {
            $existing = LateRequest::query()
                ->where('employee_id', $employee->id)
                ->whereDate('date', $validated['date'])
                ->where('type', $validated['type'])
                ->orderByDesc('id')
                ->lockForUpdate()
                ->first();

            if ($existing) {
                if (in_array((string) $existing->status, ['approved', 'rejected'], true)) {
                    return;
                }

                $existing->minutes = $minutes;
                $existing->reason = $validated['reason'] ?? null;
                if ($attachmentPath !== null) {
                    $existing->attachment_path = $attachmentPath;
                }
                $existing->status = 'pending';
                $existing->approved_by = null;
                $existing->save();

                return;
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
        });

        $existingApprovedOrRejected = LateRequest::query()
            ->where('employee_id', $employee->id)
            ->whereDate('date', $validated['date'])
            ->where('type', $validated['type'])
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

        return Redirect::route('late-requests.index')->with('status', 'late-request-created');
    }

    public function approve(Request $request, int $id): RedirectResponse
    {
        $user = $request->user();
        if (!$user?->canManageBackoffice()) {
            abort(403);
        }

        $validated = $request->validate([
            'admin_notes' => ['nullable', 'string'],
            'corrected_time_in' => ['nullable', 'date_format:H:i'],
            'corrected_break_out' => ['nullable', 'date_format:H:i'],
            'corrected_break_in' => ['nullable', 'date_format:H:i'],
            'corrected_time_out' => ['nullable', 'date_format:H:i'],
        ]);

        DB::transaction(function () use ($id, $user, $validated) {
            $lr = LateRequest::query()->where('id', $id)->lockForUpdate()->firstOrFail();

            $lr->status = 'approved';
            $lr->approved_by = optional($user->employee)->id;
            $lr->admin_notes = $validated['admin_notes'] ?? null;

            $setCorrection = false;
            foreach (['corrected_time_in', 'corrected_break_out', 'corrected_break_in', 'corrected_time_out'] as $f) {
                if (array_key_exists($f, $validated) && $validated[$f] !== null && trim((string) $validated[$f]) !== '') {
                    $lr->{$f} = trim((string) $validated[$f]) . ':00';
                    $setCorrection = true;
                }
            }

            if ($setCorrection) {
                $lr->corrected_by = optional($user->employee)->id;
                $lr->corrected_at = now();
            }

            $lr->save();

            if (!$setCorrection) {
                return;
            }

            $employee = Employee::query()->where('id', $lr->employee_id)->first();
            $employeeCode = $employee?->employee_code;
            if (!is_string($employeeCode) || trim($employeeCode) === '') {
                return;
            }

            $dateStr = optional($lr->date)->format('Y-m-d');
            if (!$dateStr) {
                $dateStr = (string) $lr->date;
            }

            $daily = AttendanceDailySummary::query()
                ->where('employee_code', $employeeCode)
                ->whereDate('summary_date', $dateStr)
                ->lockForUpdate()
                ->first();

            if (!$daily) {
                return;
            }

            $dept = (string) ($employee->department?->name ?? '');
            $calculator = new AttendanceMetricsCalculator();
            $metrics = $calculator->calculateDaily(
                $dateStr,
                $lr->corrected_time_in,
                $lr->corrected_break_out,
                $lr->corrected_break_in,
                $lr->corrected_time_out,
                $dept
            );

            $daily->time_in = $lr->corrected_time_in;
            $daily->break_out = $lr->corrected_break_out;
            $daily->break_in = $lr->corrected_break_in;
            $daily->time_out = $lr->corrected_time_out;
            $daily->grace_used = (bool) ($metrics['grace_used'] ?? false);
            $daily->late_in_minutes = (int) ($metrics['late_in_minutes'] ?? 0);
            $daily->late_break_in_minutes = (int) ($metrics['late_break_in_minutes'] ?? 0);
            $daily->undertime_break_out_minutes = (int) ($metrics['undertime_break_out_minutes'] ?? 0);
            $daily->ot_minutes = (int) ($metrics['ot_minutes'] ?? 0);
            $daily->total_hours = (float) ($metrics['total_hours'] ?? 0);
            $daily->missed_logs = (int) ($metrics['missed_logs'] ?? 0);
            $daily->status = (string) ($metrics['status'] ?? $daily->status);
            $daily->save();

            $day = Carbon::parse($dateStr)->startOfDay();
            $affectedPeriods = AttendancePeriodSummary::query()
                ->where('employee_code', $employeeCode)
                ->whereDate('period_start', '<=', $day)
                ->whereDate('period_end', '>=', $day)
                ->lockForUpdate()
                ->get();

            foreach ($affectedPeriods as $p) {
                $periodStart = optional($p->period_start)->format('Y-m-d');
                $periodEnd = optional($p->period_end)->format('Y-m-d');
                if (!$periodStart || !$periodEnd) {
                    continue;
                }

                $days = AttendanceDailySummary::query()
                    ->where('employee_code', $employeeCode)
                    ->whereDate('summary_date', '>=', $periodStart)
                    ->whereDate('summary_date', '<=', $periodEnd)
                    ->get();

                $lateFrequency = 0;
                $missedLogsCount = 0;
                $graceDays = 0;
                $absences = 0.0;
                $daysWorked = 0.0;
                $lateDuration = 0;
                $totalUndertime = 0;
                $undertimeFrequency = 0;

                $absenceValueForStatus = function ($status): float {
                    $s = strtolower(trim((string) ($status ?? '')));
                    if ($s === '') {
                        return 0.0;
                    }

                    if ($s === 'absent' || $s === 'whole day absent') {
                        return 1.0;
                    }

                    if (str_contains($s, 'absent am') || str_contains($s, 'absent pm') || str_contains($s, 'half day (incomplete')) {
                        return 0.5;
                    }

                    return 0.0;
                };

                $lateTimeCounts = [];
                foreach ($days as $d) {
                    $missedLogsCount += (int) ($d->missed_logs ?? 0);
                    if ((bool) ($d->grace_used ?? false)) {
                        $graceDays += 1;
                    }

                    $absenceValue = $absenceValueForStatus($d->status ?? null);
                    if ($absenceValue > 0) {
                        $absences += $absenceValue;
                        $daysWorked += max(1.0 - $absenceValue, 0.0);
                    } else {
                        $daysWorked += 1.0;
                    }

                    $lateIn = (int) ($d->late_in_minutes ?? 0);
                    $lateBreak = (int) ($d->late_break_in_minutes ?? 0);
                    $lateDuration += $lateIn + $lateBreak;
                    $lateFrequency += ($lateIn > 0 ? 1 : 0) + ($lateBreak > 0 ? 1 : 0);

                    $undertime = (int) ($d->undertime_break_out_minutes ?? 0);
                    $totalUndertime += $undertime;
                    if ($undertime > 0) {
                        $undertimeFrequency += 1;
                    }

                    if ($lateIn > 0 && $d->time_in) {
                        $t = (string) $d->time_in;
                        $lateTimeCounts[$t] = ($lateTimeCounts[$t] ?? 0) + 1;
                    }
                }

                $avgLate = $lateFrequency > 0 ? (float) number_format($lateDuration / $lateFrequency, 2, '.', '') : 0.0;
                $mostFrequentLateTime = null;
                if (count($lateTimeCounts) > 0) {
                    arsort($lateTimeCounts);
                    $mostFrequentLateTime = array_key_first($lateTimeCounts);
                }

                $p->late_frequency = $lateFrequency;
                $p->missed_logs_count = $missedLogsCount;
                $p->grace_days = $graceDays;
                $p->absences = $absences;
                $p->days_worked = $daysWorked;
                $p->late_duration = $lateDuration;
                $p->avg_late_per_occurrence = $avgLate;
                $p->total_undertime = $totalUndertime;
                $p->undertime_frequency = $undertimeFrequency;
                $p->most_frequent_late_time = $mostFrequentLateTime;
                $p->save();
            }
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

        $lr = LateRequest::query()->where('id', $id)->firstOrFail();
        $lr->status = 'rejected';
        $lr->approved_by = optional($user->employee)->id;
        $lr->admin_notes = $validated['admin_notes'] ?? null;
        $lr->save();

        return Redirect::back();
    }
}
