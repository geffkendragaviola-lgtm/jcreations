<?php

namespace App\Http\Controllers;

use App\Models\AttendanceDailySummary;
use App\Models\OvertimeRequest;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $absenceValueForStatus = function ($status): float {
            if ($status === null) {
                return 0.0;
            }

            $s = strtolower(trim((string) $status));
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

        $today = now()->toDateString();
        $monthStart = now()->startOfMonth()->toDateString();
        $monthEnd = now()->endOfMonth()->toDateString();

        $todaySummaries = AttendanceDailySummary::query()
            ->whereDate('summary_date', $today);

        $presentToday = (clone $todaySummaries)
            ->whereNotNull('status')
            ->whereRaw('LOWER(status) NOT LIKE ?', ['%absent%'])
            ->count();

        $lateToday = (clone $todaySummaries)
            ->whereRaw('LOWER(status) LIKE ?', ['%late%'])
            ->count();

        $absentToday = (clone $todaySummaries)
            ->get(['status'])
            ->sum(fn ($r) => $absenceValueForStatus($r->status));

        $totalHoursToday = (float) (clone $todaySummaries)->sum('total_hours');

        $monthlyLates = AttendanceDailySummary::query()
            ->whereDate('summary_date', '>=', $monthStart)
            ->whereDate('summary_date', '<=', $monthEnd)
            ->whereRaw('LOWER(status) LIKE ?', ['%late%'])
            ->count();

        $monthlyAbsences = AttendanceDailySummary::query()
            ->whereDate('summary_date', '>=', $monthStart)
            ->whereDate('summary_date', '<=', $monthEnd)
            ->get(['status'])
            ->sum(fn ($r) => $absenceValueForStatus($r->status));

        $overtimeHours = (float) OvertimeRequest::query()
            ->whereDate('date', '>=', $monthStart)
            ->whereDate('date', '<=', $monthEnd)
            ->where('status', 'approved')
            ->sum('hours');

        $onTimeDays = AttendanceDailySummary::query()
            ->whereDate('summary_date', '>=', $monthStart)
            ->whereDate('summary_date', '<=', $monthEnd)
            ->where('status', 'Ontime')
            ->count();

        $totalWorkDays = AttendanceDailySummary::query()
            ->whereDate('summary_date', '>=', $monthStart)
            ->whereDate('summary_date', '<=', $monthEnd)
            ->whereNotNull('status')
            ->count();

        $onTimePercent = $totalWorkDays > 0
            ? round(($onTimeDays / $totalWorkDays) * 100, 2)
            : 0.0;

        return view('dashboard', [
            'metrics' => [
                'present_today' => $presentToday,
                'late_today' => $lateToday,
                'absent_today' => $absentToday,
                'total_hours_today' => round($totalHoursToday, 2),
                'monthly_lates' => $monthlyLates,
                'monthly_absences' => $monthlyAbsences,
                'overtime_hours' => round($overtimeHours, 2),
                'on_time_percent' => $onTimePercent,
            ],
        ]);
    }
}
