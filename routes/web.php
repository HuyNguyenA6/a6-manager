<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\LeaveRequestController;
use App\Http\Controllers\TimesheetController;
use Illuminate\Support\Facades\Route;

Route::get('/dashboard', function () {
    return view('welcome');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/', function () {
        return view('welcome');
    });

    // Profile
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Leave Requests
    Route::get('/requests', [LeaveRequestController::class, 'index'])->name('requests.index');
    Route::post('/requests/list', [LeaveRequestController::class, 'list'])->name('requests.list');
    Route::post('/requests/edit', [LeaveRequestController::class, 'edit'])->name('requests.edit');
    Route::post('/requests/update', [LeaveRequestController::class, 'update'])->name('requests.update');

    // Timesheet
    Route::get('/timesheets', [TimesheetController::class, 'index'])->name('timesheets.index');
    Route::get('/timesheets/add', [TimesheetController::class, 'add'])->name('timesheets.add');
    Route::get('/timesheets/{id}/edit', [TimesheetController::class, 'edit'])->name('timesheets.edit');
    Route::get('/timesheets/{id}', [TimesheetController::class, 'view'])->name('timesheets.view');    
    Route::post('/timesheets/list', [TimesheetController::class, 'list'])->name('timesheets.list');
    Route::post('/timesheets/reject', [TimesheetController::class, 'reject'])->name('timesheets.reject');
    Route::post('/timesheets/store', [TimesheetController::class, 'store'])->name('timesheets.store');
    Route::put('/timesheets/{id}/update', [TimesheetController::class, 'update'])->name('timesheets.update');
    Route::delete('/timesheets/{id}/delete', [TimesheetController::class, 'delete'])->name('timesheets.delete');
    Route::post('/timesheets/add_work_hour', [TimesheetController::class, 'addWorkHourForReports'])->name('timesheets.add_work_hour');
    Route::post('/timesheets/add_work_hour_on_mobile', [TimesheetController::class, 'addWorkHourForReportsOnMobile'])->name('timesheets.add_work_hour_on_mobile');    
});

require __DIR__.'/auth.php';
