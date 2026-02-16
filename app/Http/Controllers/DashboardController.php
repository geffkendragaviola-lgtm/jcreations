<?php

namespace App\Http\Controllers;

use App\Models\AttendanceDailySummary;
use App\Models\OvertimeRequest;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $today = now()->toDateString();
        $monthStart = now()->startOfMonth()->toDateString();
        $monthEnd = now()->endOfMonth()->toDateString();

        $todaySummaries = AttendanceDailySummary::query()
            ->whereDate('summary_date', $today);

        $presentToday = (clone $todaySummaries)
            ->whereIn('status', ['ON_TIME', 'LATE', 'UNDERTIME', 'MISSED_LOG'])
            ->count();

        $lateToday = (clone $todaySummaries)
            ->where('status', 'LATE')
            ->count();

        $absentToday = (clone $todaySummaries)
            ->where('status', 'ABSENT')
            ->count();

        $totalHoursToday = (float) (clone $todaySummaries)->sum('total_hours');

        $monthlyLates = AttendanceDailySummary::query()
            ->whereDate('summary_date', '>=', $monthStart)
            ->whereDate('summary_date', '<=', $monthEnd)
            ->where('status', 'LATE')
            ->count();

        $monthlyAbsences = AttendanceDailySummary::query()
            ->whereDate('summary_date', '>=', $monthStart)
            ->whereDate('summary_date', '<=', $monthEnd)
            ->where('status', 'ABSENT')
            ->count();

        $overtimeHours = (float) OvertimeRequest::query()
            ->whereDate('date', '>=', $monthStart)
            ->whereDate('date', '<=', $monthEnd)
            ->where('status', 'approved')
            ->sum('hours');

        $onTimeDays = AttendanceDailySummary::query()
            ->whereDate('summary_date', '>=', $monthStart)
            ->whereDate('summary_date', '<=', $monthEnd)
            ->where('status', 'ON_TIME')
            ->count();

        $totalWorkDays = AttendanceDailySummary::query()
            ->whereDate('summary_date', '>=', $monthStart)
            ->whereDate('summary_date', '<=', $monthEnd)
            ->whereIn('status', ['ON_TIME', 'LATE', 'UNDERTIME', 'MISSED_LOG', 'ABSENT'])
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
