<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('login');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::get('/time-tracking', function () {
    return view('time-tracking.index');
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

Route::match(['get', 'post'], '/payroll', [\App\Http\Controllers\PayrollController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('payroll.index');

Route::get('/employees', [\App\Http\Controllers\EmployeeManagementController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('employees.index');
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
