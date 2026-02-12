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

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
