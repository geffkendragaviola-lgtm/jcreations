<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('login');
});

Route::get('/dashboard', [\App\Http\Controllers\DashboardController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::get('/time-tracking', function () {
    $user = request()->user();
    if (!$user?->canManageBackoffice()) {
        abort(403);
    }

    $departments = \App\Models\Department::query()
        ->select(['name', 'business_hours_start', 'business_hours_end'])
        ->orderBy('name')
        ->get();

    $validEmployeeCodes = \App\Models\Employee::query()
        ->whereNotNull('employee_code')
        ->where('employee_code', '!=', '')
        ->pluck('employee_code')
        ->map(fn ($c) => trim((string) $c))
        ->filter(fn ($c) => $c !== '')
        ->unique()
        ->values()
        ->all();

    $departmentSchedules = [];
    foreach ($departments as $dept) {
        $name = (string) ($dept->name ?? '');
        $key = strtolower(trim(preg_replace('/\s+/', ' ', $name)));

        $departmentSchedules[$key] = [
            'start' => $dept->business_hours_start,
            'end' => $dept->business_hours_end,
        ];
    }

    return view('time-tracking.index', [
        'departmentSchedules' => $departmentSchedules,
        'validEmployeeCodes' => $validEmployeeCodes,
    ]);
})->middleware(['auth', 'verified'])->name('time-tracking.index');

Route::post('/time-tracking/upload-csv', [\App\Http\Controllers\AttendanceCsvUploadController::class, 'store'])
    ->middleware(['auth', 'verified'])
    ->name('time-tracking.upload-csv');

Route::get('/time-tracking/summaries', [\App\Http\Controllers\AttendanceSummaryController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('time-tracking.summaries');

Route::get('/time-tracking/import-batches', [\App\Http\Controllers\AttendanceImportBatchController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('time-tracking.import-batches');

Route::get('/time-tracking/logs', [\App\Http\Controllers\AttendanceLogController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('time-tracking.logs');

Route::get('/requests', [\App\Http\Controllers\RequestsController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('requests.index');

Route::get('/users', [\App\Http\Controllers\UserManagementController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('users.index');

Route::get('/leave-requests', [\App\Http\Controllers\LeaveRequestController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('leave-requests.index');
Route::post('/leave-requests', [\App\Http\Controllers\LeaveRequestController::class, 'store'])
    ->middleware(['auth', 'verified'])
    ->name('leave-requests.store');
Route::patch('/leave-requests/{id}/approve', [\App\Http\Controllers\LeaveRequestController::class, 'approve'])
    ->middleware(['auth', 'verified'])
    ->name('leave-requests.approve');
Route::patch('/leave-requests/{id}/reject', [\App\Http\Controllers\LeaveRequestController::class, 'reject'])
    ->middleware(['auth', 'verified'])
    ->name('leave-requests.reject');

Route::get('/overtime-requests', [\App\Http\Controllers\OvertimeRequestController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('overtime-requests.index');
Route::post('/overtime-requests', [\App\Http\Controllers\OvertimeRequestController::class, 'store'])
    ->middleware(['auth', 'verified'])
    ->name('overtime-requests.store');
Route::patch('/overtime-requests/{id}/approve', [\App\Http\Controllers\OvertimeRequestController::class, 'approve'])
    ->middleware(['auth', 'verified'])
    ->name('overtime-requests.approve');
Route::patch('/overtime-requests/{id}/reject', [\App\Http\Controllers\OvertimeRequestController::class, 'reject'])
    ->middleware(['auth', 'verified'])
    ->name('overtime-requests.reject');

Route::post('/cash-advance-requests', [\App\Http\Controllers\CashAdvanceRequestController::class, 'store'])
    ->middleware(['auth', 'verified'])
    ->name('cash-advance-requests.store');
Route::patch('/cash-advance-requests/{id}/approve', [\App\Http\Controllers\CashAdvanceRequestController::class, 'approve'])
    ->middleware(['auth', 'verified'])
    ->name('cash-advance-requests.approve');
Route::patch('/cash-advance-requests/{id}/reject', [\App\Http\Controllers\CashAdvanceRequestController::class, 'reject'])
    ->middleware(['auth', 'verified'])
    ->name('cash-advance-requests.reject');

Route::post('/loan-requests', [\App\Http\Controllers\LoanRequestController::class, 'store'])
    ->middleware(['auth', 'verified'])
    ->name('loan-requests.store');
Route::patch('/loan-requests/{id}/approve', [\App\Http\Controllers\LoanRequestController::class, 'approve'])
    ->middleware(['auth', 'verified'])
    ->name('loan-requests.approve');
Route::patch('/loan-requests/{id}/reject', [\App\Http\Controllers\LoanRequestController::class, 'reject'])
    ->middleware(['auth', 'verified'])
    ->name('loan-requests.reject');

Route::get('/late-requests', [\App\Http\Controllers\LateRequestController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('late-requests.index');
Route::post('/late-requests', [\App\Http\Controllers\LateRequestController::class, 'store'])
    ->middleware(['auth', 'verified'])
    ->name('late-requests.store');
Route::patch('/late-requests/{id}/approve', [\App\Http\Controllers\LateRequestController::class, 'approve'])
    ->middleware(['auth', 'verified'])
    ->name('late-requests.approve');
Route::patch('/late-requests/{id}/reject', [\App\Http\Controllers\LateRequestController::class, 'reject'])
    ->middleware(['auth', 'verified'])
    ->name('late-requests.reject');

Route::get('/approvals', function () {
    return redirect()->route('requests.index', ['status' => 'pending']);
})->middleware(['auth', 'verified'])->name('approvals.index');

Route::match(['get', 'post'], '/payroll', [\App\Http\Controllers\PayrollController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('payroll.index');

Route::get('/employees', [\App\Http\Controllers\EmployeeManagementController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('employees.index');

Route::get('/employees/incomplete-employment', [\App\Http\Controllers\EmployeeManagementController::class, 'incompleteEmployment'])
    ->middleware(['auth', 'verified'])
    ->name('employees.incompleteEmployment');

Route::get('/employees/incomplete-compensation', [\App\Http\Controllers\EmployeeManagementController::class, 'incompleteCompensation'])
    ->middleware(['auth', 'verified'])
    ->name('employees.incompleteCompensation');

Route::get('/employees/incomplete-profile', [\App\Http\Controllers\EmployeeManagementController::class, 'incompleteProfile'])
    ->middleware(['auth', 'verified'])
    ->name('employees.incompleteProfile');

Route::get('/employees/incomplete-government-info', [\App\Http\Controllers\EmployeeManagementController::class, 'incompleteGovernmentInfo'])
    ->middleware(['auth', 'verified'])
    ->name('employees.incompleteGovernmentInfo');

Route::get('/employees/disciplinary-actions', [\App\Http\Controllers\EmployeeManagementController::class, 'disciplinaryActions'])
    ->middleware(['auth', 'verified'])
    ->name('employees.disciplinaryActions');

Route::get('/employees/{employee}/details', [\App\Http\Controllers\EmployeeManagementController::class, 'show'])
    ->middleware(['auth', 'verified'])
    ->name('employees.show');
Route::get('/employees/{employee}', function () {
    return redirect()->route('employees.index');
})->middleware(['auth', 'verified']);
Route::patch('/employees/{employee}', [\App\Http\Controllers\EmployeeManagementController::class, 'update'])
    ->middleware(['auth', 'verified'])
    ->name('employees.update');
Route::delete('/employees/{employee}', [\App\Http\Controllers\EmployeeManagementController::class, 'destroy'])
    ->middleware(['auth', 'verified'])
    ->name('employees.destroy');

Route::get('/work-schedules', [\App\Http\Controllers\WorkScheduleController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('work-schedules.index');
Route::patch('/work-schedules/{employee}', [\App\Http\Controllers\WorkScheduleController::class, 'update'])
    ->middleware(['auth', 'verified'])
    ->name('work-schedules.update');
Route::get('/work-schedules/{employee}/calendar', [\App\Http\Controllers\WorkScheduleController::class, 'calendar'])
    ->middleware(['auth', 'verified'])
    ->name('work-schedules.calendar');
Route::patch('/work-schedules/{employee}/override', [\App\Http\Controllers\WorkScheduleController::class, 'updateOverride'])
    ->middleware(['auth', 'verified'])
    ->name('work-schedules.override');

Route::get('/departments', [\App\Http\Controllers\DepartmentManagementController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('departments.index');
Route::post('/departments', [\App\Http\Controllers\DepartmentManagementController::class, 'store'])
    ->middleware(['auth', 'verified'])
    ->name('departments.store');
Route::patch('/departments/{department}', [\App\Http\Controllers\DepartmentManagementController::class, 'update'])
    ->middleware(['auth', 'verified'])
    ->name('departments.update');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::patch('/profile/employee', [\App\Http\Controllers\EmployeeProfileController::class, 'update'])->name('profile.employee.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
