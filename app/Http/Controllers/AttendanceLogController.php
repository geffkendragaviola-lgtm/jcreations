<?php

namespace App\Http\Controllers;

use App\Models\AttendanceLog;
use Illuminate\Http\Request;

class AttendanceLogController extends Controller
{
    public function index(Request $request)
    {
        $employeeCode = trim((string) $request->query('employee_code', ''));
        $activity = trim((string) $request->query('activity', ''));
        $dateStart = trim((string) $request->query('date_start', ''));
        $dateEnd = trim((string) $request->query('date_end', ''));

        $query = AttendanceLog::query()->with('employee');

        if ($employeeCode !== '') {
            $query->where('employee_code', $employeeCode);
        }

        if ($activity !== '' && in_array($activity, ['in', 'out'], true)) {
            $query->where('activity', $activity);
        }

        if ($dateStart !== '') {
            $query->whereDate('log_date', '>=', $dateStart);
        }

        if ($dateEnd !== '') {
            $query->whereDate('log_date', '<=', $dateEnd);
        }

        $logs = $query
            ->orderByDesc('log_date')
            ->orderByDesc('log_time')
            ->paginate(50)
            ->withQueryString();

        return view('time-tracking.logs', [
            'logs' => $logs,
            'filters' => [
                'employee_code' => $employeeCode,
                'activity' => $activity,
                'date_start' => $dateStart,
                'date_end' => $dateEnd,
            ],
        ]);
    }
}
