<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\EmployeeEligibilityController;
use App\Http\Controllers\TrackingController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::middleware(['auth', 'role:capitec_admin|city_reporter'])->group(function () {
    Route::get('/admin', [DashboardController::class, 'index'])->name('admin.dashboard');
});

Route::middleware(['auth', 'role:capitec_admin'])->group(function () {
    Route::get('/admin/eligibility', [EmployeeEligibilityController::class, 'index'])->name('admin.eligibility.index');
    Route::post('/admin/eligibility', [EmployeeEligibilityController::class, 'store'])->name('admin.eligibility.store');
    Route::post('/admin/eligibility/upload', [EmployeeEligibilityController::class, 'upload'])->name('admin.eligibility.upload');
    Route::patch('/admin/eligibility/{employee}/status', [EmployeeEligibilityController::class, 'updateStatus'])->name('admin.eligibility.update-status');
});

Route::get('/tracking', [TrackingController::class, 'show'])->name('tracking.show');

require __DIR__.'/auth.php';
