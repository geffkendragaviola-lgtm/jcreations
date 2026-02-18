<?php

namespace App\Services\Attendance;

use Illuminate\Support\Str;

class AttendanceMetricsCalculator
{
    public function calculateDaily(string $date, ?string $timeIn, ?string $breakOut, ?string $breakIn, ?string $timeOut, string $department): array
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
        $breakInTime = $this->createDateTime($date, '13:00:00');
        $afternoonLateStart = $this->createDateTime($date, $afternoonLateStartStr);
        $afternoonGraceEnd = $this->createDateTime($date, $afternoonGraceEndStr);
        $workEnd = $this->createDateTime($date, $scheduleEnd);

        $actualStart = $this->createDateTime($date, $timeIn);
        $actualBreakOut = $breakOut ? $this->createDateTime($date, $breakOut) : null;
        $actualBreakIn = $breakIn ? $this->createDateTime($date, $breakIn) : null;
        $actualEnd = $this->createDateTime($date, $timeOut);

        $missingPunchesCount = count(array_filter([$timeIn, $breakOut, $breakIn, $timeOut], fn ($v) => !$v));

        $lateMinutes = 0;
        $withinMorningGrace = false;
        if ($actualStart && $workStart && $morningGraceEnd && $actualStart > $workStart && $actualStart <= $morningGraceEnd) {
            $withinMorningGrace = true;
        } elseif ($actualStart && $morningLateStart && $actualStart >= $morningLateStart && $workStart) {
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
        } elseif ($actualBreakIn && $afternoonLateStart && $actualBreakIn >= $afternoonLateStart && $breakInTime) {
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

        $statusUi = 'Ontime';
        if ($missingPunchesCount > 0) {
            $statusUi = 'Incomplete Logs';
        } else {
            $totalLate = $lateMinutes + $lateBreakInMinutes;
            if ($totalLate > 0 && $undertimeMinutes > 0) {
                $statusUi = 'Late & Undertime';
            } elseif ($totalLate > 0) {
                $statusUi = 'Late';
            } elseif ($undertimeMinutes > 0) {
                $statusUi = 'Undertime';
            }
        }

        return [
            'grace_used' => (bool) ($withinMorningGrace || $withinAfternoonGrace),
            'late_in_minutes' => $lateMinutes,
            'late_break_in_minutes' => $lateBreakInMinutes,
            'undertime_break_out_minutes' => $undertimeMinutes,
            'ot_minutes' => $overtimeMinutes,
            'total_hours' => $totalHours,
            'missed_logs' => $missingPunchesCount,
            'status' => $statusUi,
        ];
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

    private function createDateTime(string $dateStr, ?string $timeStr): ?\DateTime
    {
        if (!$timeStr) {
            return null;
        }
        $dt = date_create($dateStr . ' ' . $timeStr);
        return $dt instanceof \DateTime ? $dt : null;
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
}
