<?php

namespace App\Http\Controllers;

use App\Models\AttendanceDailySummary;
use App\Models\AttendanceImportBatch;
use App\Models\AttendanceLog;
use App\Models\AttendancePeriodSummary;
use App\Models\Employee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class AttendanceCsvUploadController extends Controller
{
    public function store(Request $request)
    {
        $user = $request->user();
        if (!$user?->canManageBackoffice()) {
            abort(403);
        }

        $validated = $request->validate([
            'file' => ['required', 'file', 'mimes:csv,txt', 'max:51200'],
        ]);

        $content = file_get_contents($validated['file']->getRealPath());
        if ($content === false) {
            return response()->json(['message' => 'Unable to read uploaded file.'], 422);
        }

        $rows = $this->parseCsvToRows($content);
        if (count($rows) < 2) {
            return response()->json(['message' => 'CSV appears to be empty.'], 422);
        }

        $header = array_map(fn ($v) => $this->normalizeHeaderCell($v), $rows[0]);
        $idx = $this->getColumnIndexes($header);

        $useHeader = $idx['employee_code'] !== null
            && $idx['department'] !== null
            && $idx['employee_name'] !== null
            && $idx['time'] !== null
            && $idx['date'] !== null
            && $idx['activity'] !== null;

        $records = [];
        $minDate = null;
        $maxDate = null;

        for ($i = 1; $i < count($rows); $i++) {
            $cells = $rows[$i];
            if (count($cells) < 6) {
                continue;
            }

            $get = function (string $key) use ($cells, $idx, $useHeader) {
                $fallback = [
                    'employee_code' => 0,
                    'department' => 1,
                    'employee_name' => 2,
                    'time' => 3,
                    'date' => 4,
                    'activity' => 5,
                    'remarks' => 6,
                ];

                $pos = $useHeader ? ($idx[$key] ?? null) : ($fallback[$key] ?? null);
                if ($pos === null) {
                    return '';
                }

                return isset($cells[$pos]) ? trim((string) $cells[$pos]) : '';
            };

            $employeeCode = $get('employee_code');
            $department = $this->normalizeDepartment($get('department'));
            $employeeName = $get('employee_name');
            $rawTime = $get('time');
            $rawDate = $get('date');
            $activity = $this->normalizeActivity($get('activity'));
            $remarks = $get('remarks');

            if ($employeeCode === '' || $rawTime === '' || $rawDate === '' || $activity === '') {
                continue;
            }

            $date = $this->normalizeDateForGrouping($rawDate);
            $time = $this->normalizeTimeTo24($rawTime);
            if ($date === null || $time === null) {
                continue;
            }

            $records[] = [
                'employee_code' => $employeeCode,
                'department' => $department,
                'employee_name' => $employeeName,
                'date' => $date,
                'time' => $time,
                'activity' => $activity,
                'remarks' => $remarks,
            ];

            $minDate = $minDate === null ? $date : min($minDate, $date);
            $maxDate = $maxDate === null ? $date : max($maxDate, $date);
        }

        $records = $this->dedupeExactRecords($records);

        if (count($records) === 0) {
            return response()->json(['message' => 'No valid rows found in CSV.'], 422);
        }

        $employeeCodes = collect($records)->pluck('employee_code')->unique()->values()->all();
        $knownEmployees = Employee::query()
            ->whereIn('employee_code', $employeeCodes)
            ->pluck('employee_code')
            ->all();

        $knownEmployees = array_flip($knownEmployees);

        $records = array_values(array_filter($records, fn ($r) => isset($knownEmployees[$r['employee_code']])));
        if (count($records) === 0) {
            return response()->json(['message' => 'No rows matched existing employees.'], 422);
        }

        $now = now();

        $batch = AttendanceImportBatch::query()
            ->whereDate('date_start', $minDate)
            ->whereDate('date_end', $maxDate)
            ->first();

        if ($batch) {
            $batch->fill([
                'source_filename' => $validated['file']->getClientOriginalName(),
                'uploaded_by' => optional($request->user())->id,
            ]);
            $batch->touch();
            $batch->save();
        } else {
            $batch = AttendanceImportBatch::query()->create([
                'uuid' => (string) Str::uuid(),
                'source_filename' => $validated['file']->getClientOriginalName(),
                'date_start' => $minDate,
                'date_end' => $maxDate,
                'uploaded_by' => optional($request->user())->id,
            ]);
        }

        $logRows = array_map(function ($r) use ($now) {
            return [
                'import_batch_id' => null,
                'employee_code' => $r['employee_code'],
                'department_snapshot' => $r['department'] !== '' ? $r['department'] : null,
                'employee_name_snapshot' => $r['employee_name'] !== '' ? $r['employee_name'] : null,
                'log_date' => $r['date'],
                'log_time' => $r['time'],
                'activity' => $r['activity'],
                'punch_type' => null,
                'image' => null,
                'address' => $r['remarks'] !== '' ? $r['remarks'] : null,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }, $records);

        $processedDaily = $this->processDaily($records);

        $dailyRows = array_map(function ($d) use ($now) {
            $missing = $d['missed_logs'];
            $status = $this->mapStatusToDb($d['status'], $missing, $d['is_absent']);

            return [
                'import_batch_id' => null,
                'employee_code' => $d['employee_code'],
                'summary_date' => $d['date'],
                'time_in' => $d['time_in'],
                'break_out' => $d['break_out'],
                'break_in' => $d['break_in'],
                'time_out' => $d['time_out'],
                'grace_used' => $d['grace_used'],
                'late_in_minutes' => $d['late_in_minutes'],
                'undertime_break_out_minutes' => $d['undertime_minutes'],
                'late_break_in_minutes' => $d['late_break_in_minutes'],
                'ot_minutes' => $d['ot_minutes'],
                'total_hours' => $d['total_hours'],
                'missed_logs' => $missing,
                'status' => $status,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }, $processedDaily);

        $periodRows = $this->buildPeriodSummaries($dailyRows, $minDate, $maxDate, $now);

        $logRows = array_map(fn ($r) => array_merge($r, ['import_batch_id' => $batch->id]), $logRows);
        $dailyRows = array_map(fn ($r) => array_merge($r, ['import_batch_id' => $batch->id]), $dailyRows);
        $periodRows = array_map(fn ($r) => array_merge($r, ['import_batch_id' => $batch->id]), $periodRows);

        $affectedEmployeeCodes = collect($logRows)
            ->pluck('employee_code')
            ->unique()
            ->values()
            ->all();

        DB::transaction(function () use ($batch, $logRows, $dailyRows, $periodRows, $affectedEmployeeCodes, $minDate, $maxDate) {
            // Overwrite behavior: if the new CSV includes dates that already exist in the database,
            // delete the overlapping rows (across ALL batches) and then insert the new ones.
            AttendanceLog::query()
                ->whereIn('employee_code', $affectedEmployeeCodes)
                ->whereDate('log_date', '>=', $minDate)
                ->whereDate('log_date', '<=', $maxDate)
                ->delete();

            AttendanceDailySummary::query()
                ->whereIn('employee_code', $affectedEmployeeCodes)
                ->whereDate('summary_date', '>=', $minDate)
                ->whereDate('summary_date', '<=', $maxDate)
                ->delete();

            // Period summaries may overlap the incoming range even if their period_start/period_end
            // doesn't exactly match the new upload range.
            AttendancePeriodSummary::query()
                ->whereIn('employee_code', $affectedEmployeeCodes)
                ->whereDate('period_start', '<=', $maxDate)
                ->whereDate('period_end', '>=', $minDate)
                ->delete();

            AttendanceLog::query()->upsert(
                $logRows,
                ['import_batch_id', 'employee_code', 'log_date', 'log_time', 'activity'],
                ['department_snapshot', 'employee_name_snapshot', 'punch_type', 'image', 'address', 'updated_at']
            );

            AttendanceDailySummary::query()->upsert(
                $dailyRows,
                ['import_batch_id', 'employee_code', 'summary_date'],
                [
                    'time_in',
                    'break_out',
                    'break_in',
                    'time_out',
                    'grace_used',
                    'late_in_minutes',
                    'undertime_break_out_minutes',
                    'late_break_in_minutes',
                    'ot_minutes',
                    'total_hours',
                    'missed_logs',
                    'status',
                    'updated_at',
                ]
            );

            AttendancePeriodSummary::query()->upsert(
                $periodRows,
                ['import_batch_id', 'employee_code', 'period_start', 'period_end'],
                [
                    'late_frequency',
                    'missed_logs_count',
                    'grace_days',
                    'absences',
                    'days_worked',
                    'late_duration',
                    'avg_late_per_occurrence',
                    'total_undertime',
                    'undertime_frequency',
                    'most_frequent_late_time',
                    'updated_at',
                ]
            );
        });

        return response()->json([
            'message' => 'CSV processed and summaries saved.',
            'batch_uuid' => $batch->uuid,
            'date_range' => ['start' => $minDate, 'end' => $maxDate],
            'counts' => [
                'logs' => count($logRows),
                'daily_summaries' => count($dailyRows),
                'period_summaries' => count($periodRows),
            ],
        ]);
    }

    private function parseCsvToRows(string $csvContent): array
    {
        $csvContent = str_replace(["\r\n", "\r"], "\n", $csvContent);
        $lines = array_values(array_filter(explode("\n", $csvContent), fn ($l) => trim($l) !== ''));
        $rows = [];
        foreach ($lines as $line) {
            $rows[] = str_getcsv($line);
        }
        return $rows;
    }

    private function normalizeHeaderCell(string $cell): string
    {
        return preg_replace('/\s+/', ' ', trim(Str::lower($cell)));
    }

    private function getColumnIndexes(array $header): array
    {
        $find = function (array $names) use ($header) {
            foreach ($names as $name) {
                $n = preg_replace('/\s+/', ' ', trim(Str::lower($name)));
                $pos = array_search($n, $header, true);
                if ($pos !== false) {
                    return $pos;
                }
            }
            return null;
        };

        return [
            'employee_code' => $find(['employee id', 'employeeid', 'emp id', 'id', 'employee code', 'employee_code']),
            'department' => $find(['department', 'dept']),
            'employee_name' => $find(['employee name', 'employeename', 'name']),
            'time' => $find(['time']),
            'date' => $find(['date']),
            'activity' => $find(['activity']),
            'remarks' => $find(['remarks', 'image', 'address', 'location']),
        ];
    }

    private function normalizeActivity(string $activityStr): string
    {
        $v = Str::lower(trim($activityStr));
        if ($v === 'in' || $v === 'time in' || $v === 'clock in') {
            return 'in';
        }
        if ($v === 'out' || $v === 'time out' || $v === 'clock out') {
            return 'out';
        }
        return '';
    }

    private function normalizeDepartment(string $dept): string
    {
        $raw = trim($dept);
        if ($raw === '') {
            return '';
        }

        $key = preg_replace('/\s+/', ' ', Str::lower($raw));
        if ($key === 'ct print stop' || $key === 'ct print shop') {
            return 'CT Print Shop';
        }
        if ($key === 'jct') {
            return 'JCT';
        }
        if ($key === 'jct print stop') {
            return 'JCT Print Stop';
        }
        if ($key === 'eco trade' || $key === 'ecotrade') {
            return 'Ecotrade';
        }
        if ($key === 'shop / eco') {
            return 'Shop / Eco';
        }
        if ($key === 'shop') {
            return 'Shop';
        }

        return $raw;
    }

    private function normalizeDateForGrouping(string $dateStr): ?string
    {
        $s = trim($dateStr);
        if ($s === '') {
            return null;
        }

        if (preg_match('/^(\d{4})-(\d{2})-(\d{2})$/', $s, $m)) {
            return $m[1] . '-' . $m[2] . '-' . $m[3];
        }

        if (preg_match('/^(\d{1,2})\/(\d{1,2})\/(\d{4})$/', $s, $m)) {
            $month = (int) $m[1];
            $day = (int) $m[2];
            $year = (int) $m[3];
            if ($month >= 1 && $month <= 12 && $day >= 1 && $day <= 31) {
                return sprintf('%04d-%02d-%02d', $year, $month, $day);
            }
        }

        $ts = strtotime($s);
        if ($ts === false) {
            return null;
        }

        return date('Y-m-d', $ts);
    }

    private function normalizeTimeTo24(string $timeStr): ?string
    {
        $cleaned = preg_replace('/\s+/', ' ', trim((string) $timeStr));
        if ($cleaned === '') {
            return null;
        }

        $withoutMs = preg_replace('/\.\d+$/', '', $cleaned);

        if (preg_match('/^(\d{1,2})(?::(\d{2}))?(?::(\d{2}))?\s*(am|pm)$/i', $withoutMs, $m)) {
            $hour = (int) $m[1];
            $minute = (int) ($m[2] ?? '0');
            $second = (int) ($m[3] ?? '0');
            $period = Str::lower($m[4]);

            if ($period === 'am' && $hour === 12) {
                $hour = 0;
            }
            if ($period === 'pm' && $hour < 12) {
                $hour += 12;
            }

            if (!$this->isValidTime($hour, $minute, $second)) {
                return null;
            }

            return sprintf('%02d:%02d:%02d', $hour, $minute, $second);
        }

        if (preg_match('/^(\d{1,2}):(\d{2})(?::(\d{2}))?$/', $withoutMs, $m)) {
            $hour = (int) $m[1];
            $minute = (int) $m[2];
            $second = (int) ($m[3] ?? '0');
            if (!$this->isValidTime($hour, $minute, $second)) {
                return null;
            }
            return sprintf('%02d:%02d:%02d', $hour, $minute, $second);
        }

        return null;
    }

    private function isValidTime(int $hour, int $minute, int $second): bool
    {
        return $hour >= 0 && $hour <= 23 && $minute >= 0 && $minute <= 59 && $second >= 0 && $second <= 59;
    }

    private function dedupeExactRecords(array $records): array
    {
        $seen = [];
        $out = [];
        foreach ($records as $r) {
            $key = implode('|', [
                $r['employee_code'],
                $r['employee_name'],
                $r['department'],
                $r['date'],
                $r['time'],
                $r['activity'],
            ]);
            if (isset($seen[$key])) {
                continue;
            }
            $seen[$key] = true;
            $out[] = $r;
        }
        return $out;
    }

    private function processDaily(array $records): array
    {
        $grouped = [];

        foreach ($records as $r) {
            $key = $r['employee_code'] . '-' . $r['date'];
            if (!isset($grouped[$key])) {
                $grouped[$key] = [
                    'employee_code' => $r['employee_code'],
                    'department' => $r['department'],
                    'date' => $r['date'],
                    'activities' => [],
                ];
            }
            $grouped[$key]['activities'][] = [
                'time' => $r['time'],
                'minutes' => $this->timeToMinutes($r['time']),
                'activity' => $r['activity'],
            ];
        }

        $out = [];
        foreach ($grouped as $g) {
            $activities = array_values(array_filter($g['activities'], fn ($a) => $a['minutes'] !== null));
            usort($activities, fn ($a, $b) => $a['minutes'] <=> $b['minutes']);

            $activities = $this->dedupeNearbyPunches($activities, 2);

            $slots = $this->selectDailySlots($activities);

            $timeIn = $slots['morningIn']['time'] ?? null;
            $breakOut = $slots['morningOut']['time'] ?? null;
            $breakIn = $slots['afternoonIn']['time'] ?? null;
            $timeOut = $slots['afternoonOut']['time'] ?? null;

            $metrics = $this->calculateMetrics($g['date'], $timeIn, $breakOut, $breakIn, $timeOut, $g['department']);

            $missing = count(array_filter([$timeIn, $breakOut, $breakIn, $timeOut], fn ($v) => !$v));

            $out[] = [
                'employee_code' => $g['employee_code'],
                'date' => $g['date'],
                'time_in' => $timeIn,
                'break_out' => $breakOut,
                'break_in' => $breakIn,
                'time_out' => $timeOut,
                'grace_used' => (bool) ($metrics['withinMorningGrace'] || $metrics['withinAfternoonGrace']),
                'late_in_minutes' => (int) $metrics['lateMinutes'],
                'late_break_in_minutes' => (int) $metrics['lateBreakInMinutes'],
                'undertime_minutes' => (int) $metrics['undertimeMinutes'],
                'ot_minutes' => (int) $metrics['overtimeMinutes'],
                'total_hours' => (float) $metrics['totalHours'],
                'status' => (string) $metrics['status'],
                'missed_logs' => $missing,
                'is_absent' => Str::contains(Str::lower((string) $metrics['status']), 'absent'),
            ];
        }

        return $out;
    }

    private function dedupeNearbyPunches(array $activities, int $windowMinutes): array
    {
        if (count($activities) === 0 || $windowMinutes <= 0) {
            return $activities;
        }

        $out = [];
        $lastKept = -9999;
        foreach ($activities as $a) {
            $m = $a['minutes'];
            if ($m === null) {
                continue;
            }
            if ($m - $lastKept >= $windowMinutes) {
                $out[] = $a;
                $lastKept = $m;
            }
        }

        return $out;
    }

    private function timeToMinutes(string $timeStr): ?float
    {
        $normalized = $this->normalizeTimeTo24($timeStr);
        if ($normalized === null) {
            return null;
        }
        $parts = array_map('intval', explode(':', $normalized));
        $hour = $parts[0] ?? 0;
        $minute = $parts[1] ?? 0;
        $second = $parts[2] ?? 0;
        return $hour * 60 + $minute + ($second / 60);
    }

    private function selectDailySlots(array $activities): array
    {
        $times = $activities;
        if (count($times) === 0) {
            return ['morningIn' => null, 'morningOut' => null, 'afternoonIn' => null, 'afternoonOut' => null];
        }

        $breakOutTime = $this->timeToMinutes('12:00:00');
        $breakOutLatest = $this->timeToMinutes('12:44:59');
        $workStart = $this->timeToMinutes('08:00:00');
        $breakInTime = $this->timeToMinutes('13:00:00');

        $morningIn = null;
        if ($breakOutTime !== null) {
            foreach ($times as $t) {
                if ($t['minutes'] !== null && $t['minutes'] >= 0 && $t['minutes'] <= $breakOutTime) {
                    $morningIn = $t;
                    break;
                }
            }
        }

        $timeInMin = $morningIn['minutes'] ?? null;
        $minForBreakOut = $timeInMin !== null ? $timeInMin + 120 : null;

        $morningOut = $this->selectClosestInWindow(
            $times,
            '08:00:00',
            '12:44:59',
            '12:00:00',
            $minForBreakOut
        );

        $breakOutMin = $morningOut['minutes'] ?? null;
        $breakInWindowStart = $morningOut ? '12:31:00' : '12:45:00';
        $minForBreakIn = $breakOutMin !== null ? $breakOutMin + 15 : null;

        $targetBreakInMin = $this->timeToMinutes('13:00:00');
        $afternoonIn = null;
        if ($targetBreakInMin !== null) {
            $startMinutes = $this->timeToMinutes($breakInWindowStart);
            $endMinutes = $this->timeToMinutes('16:59:00');
            if ($startMinutes !== null && $endMinutes !== null) {
                $candidates = array_values(array_filter($times, function ($t) use ($startMinutes, $endMinutes, $minForBreakIn) {
                    if ($t['minutes'] === null) {
                        return false;
                    }
                    if ($t['minutes'] < $startMinutes || $t['minutes'] > $endMinutes) {
                        return false;
                    }
                    if ($minForBreakIn !== null && $t['minutes'] < $minForBreakIn) {
                        return false;
                    }
                    return true;
                }));

                if (count($candidates) > 0) {
                    $best = null;
                    $bestScore = INF;
                    foreach ($candidates as $t) {
                        $score = abs($t['minutes'] - $targetBreakInMin);
                        if ($score < $bestScore) {
                            $bestScore = $score;
                            $best = $t;
                        }
                    }
                    if ($best !== null && $bestScore <= 180) {
                        $afternoonIn = $best;
                    }
                }
            }
        }

        $afternoonOut = null;
        $startMinutes = $this->timeToMinutes('13:00:00');
        $endMinutes = $this->timeToMinutes('23:59:59');
        if ($startMinutes !== null && $endMinutes !== null) {
            $minOut = $afternoonIn && isset($afternoonIn['minutes'])
                ? max($startMinutes, $afternoonIn['minutes'] + 60)
                : $startMinutes;

            $candidates = array_values(array_filter($times, fn ($t) => $t['minutes'] !== null && $t['minutes'] >= $minOut && $t['minutes'] <= $endMinutes));
            if (count($candidates) > 0) {
                $afternoonOut = $candidates[count($candidates) - 1];
            }
        }

        return ['morningIn' => $morningIn, 'morningOut' => $morningOut, 'afternoonIn' => $afternoonIn, 'afternoonOut' => $afternoonOut];
    }

    private function selectClosestInWindow(array $times, string $windowStart, string $windowEnd, string $targetTime, ?float $minMinutes): ?array
    {
        $startMinutes = $this->timeToMinutes($windowStart);
        $endMinutes = $this->timeToMinutes($windowEnd);
        $targetMinutes = $this->timeToMinutes($targetTime);
        if ($startMinutes === null || $endMinutes === null || $targetMinutes === null) {
            return null;
        }

        $best = null;
        $bestScore = INF;

        foreach ($times as $t) {
            if ($t['minutes'] === null) {
                continue;
            }
            if ($t['minutes'] < $startMinutes || $t['minutes'] > $endMinutes) {
                continue;
            }
            if ($minMinutes !== null && $t['minutes'] < $minMinutes) {
                continue;
            }
            $score = abs($t['minutes'] - $targetMinutes);
            if ($score < $bestScore) {
                $bestScore = $score;
                $best = $t;
            }
        }

        return $best;
    }

    private function getScheduleForDepartment(string $department): array
    {
        $d = preg_replace('/\s+/', ' ', Str::lower(trim($department)));

        if ($d === 'shop') {
            return ['start' => '08:00:00', 'end' => '17:00:00'];
        }
        if ($d === 'ct print shop' || $d === 'ecotrade' || $d === 'shop / eco' || $d === 'shop/eco') {
            return ['start' => '08:30:00', 'end' => '17:30:00'];
        }
        if ($d === 'jct') {
            return ['start' => '09:00:00', 'end' => '18:00:00'];
        }

        return ['start' => '08:00:00', 'end' => '17:00:00'];
    }

    private function addMinutesToTimeString(string $timeStr, int $minutesToAdd): string
    {
        if (!preg_match('/^(\d{1,2}):(\d{2}):(\d{2})$/', $timeStr, $m)) {
            return $timeStr;
        }

        $hh = (int) $m[1];
        $mm = (int) $m[2];
        $ss = (int) $m[3];
        $base = $hh * 60 + $mm + ($ss >= 30 ? 1 : 0);
        $next = ($base + $minutesToAdd + 24 * 60) % (24 * 60);
        $nh = (int) floor($next / 60);
        $nm = $next % 60;

        return sprintf('%02d:%02d:00', $nh, $nm);
    }

    private function setTimeStringSeconds(string $timeStr, int $seconds): string
    {
        if (!preg_match('/^(\d{1,2}):(\d{2}):(\d{2})$/', $timeStr, $m)) {
            return $timeStr;
        }

        $hh = sprintf('%02d', (int) $m[1]);
        $mm = sprintf('%02d', (int) $m[2]);
        $ss = sprintf('%02d', max(0, min(59, $seconds)));
        return $hh . ':' . $mm . ':' . $ss;
    }

    private function calculateMetrics(string $date, ?string $timeIn, ?string $breakOut, ?string $breakIn, ?string $timeOut, string $department): array
    {
        $schedule = $this->getScheduleForDepartment($department);
        $scheduleStart = $schedule['start'];
        $scheduleEnd = $schedule['end'];

        $morningLateStartStr = $this->addMinutesToTimeString($scheduleStart, 16);
        $morningGraceEndStr = $this->setTimeStringSeconds($this->addMinutesToTimeString($scheduleStart, 15), 59);
        $afternoonLateStartStr = $this->addMinutesToTimeString('13:00:00', 16);
        $afternoonGraceEndStr = $this->setTimeStringSeconds($this->addMinutesToTimeString('13:00:00', 15), 59);

        $workStart = $this->createDateTime($date, $scheduleStart);
        $morningLateStart = $this->createDateTime($date, $morningLateStartStr);
        $morningGraceEnd = $this->createDateTime($date, $morningGraceEndStr);
        $breakOutTime = $this->createDateTime($date, '12:00:00');
        $breakInEarliest = $this->createDateTime($date, '12:45:00');
        $breakInTime = $this->createDateTime($date, '13:00:00');
        $afternoonLateStart = $this->createDateTime($date, $afternoonLateStartStr);
        $afternoonGraceEnd = $this->createDateTime($date, $afternoonGraceEndStr);
        $workEnd = $this->createDateTime($date, $scheduleEnd);

        $actualStart = $this->createDateTime($date, $timeIn);
        $actualBreakOut = $breakOut ? $this->createDateTime($date, $breakOut) : null;
        $actualBreakIn = $breakIn ? $this->createDateTime($date, $breakIn) : null;
        $actualEnd = $this->createDateTime($date, $timeOut);

        $missingPunchesCount = count(array_filter([$timeIn, $breakOut, $breakIn, $timeOut], fn ($v) => !$v));

        if (!$workStart || !$workEnd) {
            return [
                'lateMinutes' => 0,
                'lateBreakInMinutes' => 0,
                'undertimeMinutes' => 0,
                'overtimeMinutes' => 0,
                'withinMorningGrace' => false,
                'withinAfternoonGrace' => false,
                'totalHours' => 0.0,
                'status' => 'Invalid Time',
            ];
        }

        $absentAM = !$actualStart && !$actualBreakOut;
        $absentPM = !$actualBreakIn && !$actualEnd;
        $hasTimeIn = (bool) $actualStart;
        $hasTimeOut = (bool) $actualEnd;
        $halfDayIncomplete = ($hasTimeIn && !$hasTimeOut) || (!$hasTimeIn && $hasTimeOut);

        $lateMinutes = 0;
        $withinMorningGrace = false;
        if ($actualStart && $workStart && $morningGraceEnd && $actualStart > $workStart && $actualStart <= $morningGraceEnd) {
            $withinMorningGrace = true;
        } elseif ($actualStart && $morningLateStart && $actualStart >= $morningLateStart) {
            $lateMinutes = (int) round(($actualStart->getTimestamp() - $workStart->getTimestamp()) / 60);
        }

        $undertimeBreakOutMinutes = 0;
        if ($actualBreakOut && $breakOutTime) {
            $diffMin = (int) round(($breakOutTime->getTimestamp() - $actualBreakOut->getTimestamp()) / 60);
            if ($diffMin > 16) {
                $undertimeBreakOutMinutes = $diffMin;
            }
        }

        $lateBreakInMinutes = 0;
        $withinAfternoonGrace = false;
        if ($actualBreakIn && $breakInTime && $afternoonGraceEnd && $actualBreakIn > $breakInTime && $actualBreakIn <= $afternoonGraceEnd) {
            $withinAfternoonGrace = true;
        } elseif ($actualBreakIn && $afternoonLateStart && $actualBreakIn >= $afternoonLateStart) {
            $lateBreakInMinutes = (int) round(($actualBreakIn->getTimestamp() - $breakInTime->getTimestamp()) / 60);
        }

        $overtimeMinutes = 0;
        if ($actualEnd && $workEnd && $actualEnd > $workEnd) {
            $overtimeMinutes = (int) round(($actualEnd->getTimestamp() - $workEnd->getTimestamp()) / 60);
        }

        $undertimeTimeOutMinutes = 0;
        if ($actualEnd && $workEnd) {
            $diffMin = (int) round(($workEnd->getTimestamp() - $actualEnd->getTimestamp()) / 60);
            if ($diffMin > 16) {
                $undertimeTimeOutMinutes = $diffMin;
            }
        }

        $undertimeMinutes = $undertimeBreakOutMinutes + $undertimeTimeOutMinutes;

        $totalHours = 0.0;
        if ($actualStart && $actualEnd) {
            $totalMinutes = (int) round(($actualEnd->getTimestamp() - $actualStart->getTimestamp()) / 60);
            $netMinutes = max($totalMinutes - 60, 0);
            $totalHours = (float) number_format($netMinutes / 60, 2, '.', '');
        }

        if ($absentAM || $absentPM) {
            $status = ($absentAM && $absentPM) ? 'Whole Day Absent' : trim(($absentAM ? 'Absent AM' : '') . ($absentAM && $absentPM ? ' / ' : '') . ($absentPM ? 'Absent PM' : ''));
        } elseif ($missingPunchesCount === 1) {
            $status = 'Incomplete Logs';
        } elseif ($halfDayIncomplete) {
            $status = 'Half Day (Incomplete Logs)';
        } else {
            $status = 'Ontime';
            $totalLate = $lateMinutes + $lateBreakInMinutes;
            if ($totalLate > 0 && $undertimeMinutes > 0) {
                $status = 'Late & Undertime';
            } elseif ($totalLate > 0) {
                $status = 'Late';
            } elseif ($undertimeMinutes > 0) {
                $status = 'Undertime';
            }
        }

        return [
            'lateMinutes' => $lateMinutes,
            'lateBreakInMinutes' => $lateBreakInMinutes,
            'undertimeMinutes' => $undertimeMinutes,
            'overtimeMinutes' => $overtimeMinutes,
            'withinMorningGrace' => $withinMorningGrace,
            'withinAfternoonGrace' => $withinAfternoonGrace,
            'totalHours' => $totalHours,
            'status' => $status,
        ];
    }

    private function createDateTime(string $dateStr, ?string $timeStr): ?\DateTime
    {
        if (!$timeStr) {
            return null;
        }
        $timeValue = $this->normalizeTimeTo24($timeStr) ?? $timeStr;
        $dt = date_create($dateStr . ' ' . $timeValue);
        return $dt instanceof \DateTime ? $dt : null;
    }

    private function mapStatusToDb(string $jsStatus, int $missingPunchesCount, bool $isAbsent): string
    {
        if ($isAbsent) {
            return 'ABSENT';
        }

        $statusLower = Str::lower($jsStatus);
        if (Str::contains($statusLower, 'incomplete') || $missingPunchesCount > 0) {
            return 'MISSED_LOG';
        }

        if (Str::contains($statusLower, 'late')) {
            return 'LATE';
        }

        if (Str::contains($statusLower, 'undertime')) {
            return 'UNDERTIME';
        }

        return 'ON_TIME';
    }

    private function buildPeriodSummaries(array $dailyRows, string $periodStart, string $periodEnd, $now): array
    {
        $byEmployee = [];
        foreach ($dailyRows as $d) {
            $byEmployee[$d['employee_code']][] = $d;
        }

        $out = [];
        foreach ($byEmployee as $employeeCode => $days) {
            $lateFrequency = 0;
            $missedLogsCount = 0;
            $graceDays = 0;
            $absences = 0;
            $daysWorked = 0;
            $lateDuration = 0;
            $totalUndertime = 0;
            $undertimeFrequency = 0;

            $lateTimeCounts = [];
            foreach ($days as $d) {
                $missedLogsCount += (int) ($d['missed_logs'] ?? 0);
                if (!empty($d['grace_used'])) {
                    $graceDays += 1;
                }

                if (($d['status'] ?? '') === 'ABSENT') {
                    $absences += 1;
                } else {
                    $daysWorked += 1;
                }

                $lateIn = (int) ($d['late_in_minutes'] ?? 0);
                $lateBreak = (int) ($d['late_break_in_minutes'] ?? 0);

                $lateDuration += $lateIn + $lateBreak;
                $lateFrequency += ($lateIn > 0 ? 1 : 0) + ($lateBreak > 0 ? 1 : 0);

                $undertime = (int) ($d['undertime_break_out_minutes'] ?? 0);
                $totalUndertime += $undertime;
                if ($undertime > 0) {
                    $undertimeFrequency += 1;
                }

                if ($lateIn > 0 && !empty($d['time_in'])) {
                    $t = $d['time_in'];
                    $lateTimeCounts[$t] = ($lateTimeCounts[$t] ?? 0) + 1;
                }
            }

            $avgLate = $lateFrequency > 0 ? (float) number_format($lateDuration / $lateFrequency, 2, '.', '') : 0.0;

            $mostFrequentLateTime = null;
            if (count($lateTimeCounts) > 0) {
                arsort($lateTimeCounts);
                $mostFrequentLateTime = array_key_first($lateTimeCounts);
            }

            $out[] = [
                'employee_code' => $employeeCode,
                'period_start' => $periodStart,
                'period_end' => $periodEnd,
                'late_frequency' => $lateFrequency,
                'missed_logs_count' => $missedLogsCount,
                'grace_days' => $graceDays,
                'absences' => $absences,
                'days_worked' => $daysWorked,
                'late_duration' => $lateDuration,
                'avg_late_per_occurrence' => $avgLate,
                'total_undertime' => $totalUndertime,
                'undertime_frequency' => $undertimeFrequency,
                'most_frequent_late_time' => $mostFrequentLateTime,
                'letter_generated' => false,
                'letter_reference' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        return $out;
    }
}
