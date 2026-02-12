<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\EmployeeEligibilityController;
use App\Http\Controllers\Admin\AccessRequestController as AdminAccessRequestController;
use App\Http\Controllers\Admin\AuditLogController;
use App\Http\Controllers\Admin\ScheduleController;
use App\Http\Controllers\Admin\ServiceMessageController;
use App\Http\Controllers\Admin\UserManagementController;
use App\Http\Controllers\Admin\ReportingController;
use App\Http\Controllers\Admin\ReportExportController;
use App\Http\Controllers\Admin\TwoFactorController;
use App\Http\Controllers\Admin\SettingsController;
use App\Http\Controllers\TrackingController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    $user = request()->user();
    if ($user && $user->hasAnyRole(['capitec_admin', 'city_reporter'])) {
        return redirect()->route('admin.dashboard');
    }

    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::middleware(['auth', 'role:capitec_admin|city_reporter', 'two_factor', 'admin_audit'])->group(function () {
    Route::get('/admin', [DashboardController::class, 'index'])->name('admin.dashboard');
    Route::get('/admin/reports', [ReportingController::class, 'index'])->name('admin.reports.index');
    Route::post('/admin/reports/export', [ReportExportController::class, 'export'])->name('admin.reports.export');
});

Route::middleware(['auth', 'role:capitec_admin', 'two_factor', 'admin_audit'])->group(function () {
    Route::get('/admin/users', [UserManagementController::class, 'index'])->name('admin.users.index');
    Route::get('/admin/users/create', [UserManagementController::class, 'create'])->name('admin.users.create');
    Route::post('/admin/users', [UserManagementController::class, 'store'])->name('admin.users.store');
    Route::get('/admin/users/{user}/edit', [UserManagementController::class, 'edit'])->name('admin.users.edit');
    Route::patch('/admin/users/{user}', [UserManagementController::class, 'update'])->name('admin.users.update');
    Route::patch('/admin/users/{user}/status', [UserManagementController::class, 'updateStatus'])->name('admin.users.update-status');
    Route::delete('/admin/users/{user}', [UserManagementController::class, 'destroy'])->name('admin.users.destroy');

    Route::get('/admin/eligibility', [EmployeeEligibilityController::class, 'index'])->name('admin.eligibility.index');
    Route::get('/admin/eligibility/create', [EmployeeEligibilityController::class, 'create'])->name('admin.eligibility.create');
    Route::post('/admin/eligibility', [EmployeeEligibilityController::class, 'store'])->name('admin.eligibility.store');
    Route::post('/admin/eligibility/upload', [EmployeeEligibilityController::class, 'upload'])->name('admin.eligibility.upload');
    Route::get('/admin/eligibility/{employee}/edit', [EmployeeEligibilityController::class, 'edit'])->name('admin.eligibility.edit');
    Route::patch('/admin/eligibility/{employee}', [EmployeeEligibilityController::class, 'update'])->name('admin.eligibility.update');
    Route::patch('/admin/eligibility/{employee}/status', [EmployeeEligibilityController::class, 'updateStatus'])->name('admin.eligibility.update-status');

    Route::get('/admin/access-requests', [AdminAccessRequestController::class, 'index'])->name('admin.access-requests.index');
    Route::patch('/admin/access-requests/{accessRequest}/approve', [AdminAccessRequestController::class, 'approve'])->name('admin.access-requests.approve');
    Route::patch('/admin/access-requests/{accessRequest}/decline', [AdminAccessRequestController::class, 'decline'])->name('admin.access-requests.decline');

    Route::get('/admin/schedules', [ScheduleController::class, 'index'])->name('admin.schedules.index');
    Route::get('/admin/schedules/create', [ScheduleController::class, 'create'])->name('admin.schedules.create');
    Route::post('/admin/schedules', [ScheduleController::class, 'store'])->name('admin.schedules.store');
    Route::get('/admin/schedules/{schedule}/edit', [ScheduleController::class, 'edit'])->name('admin.schedules.edit');
    Route::patch('/admin/schedules/{schedule}', [ScheduleController::class, 'update'])->name('admin.schedules.update');

    Route::get('/admin/service-messages', [ServiceMessageController::class, 'index'])->name('admin.service-messages.index');
    Route::get('/admin/service-messages/create', [ServiceMessageController::class, 'create'])->name('admin.service-messages.create');
    Route::post('/admin/service-messages', [ServiceMessageController::class, 'store'])->name('admin.service-messages.store');
    Route::get('/admin/service-messages/{serviceMessage}/edit', [ServiceMessageController::class, 'edit'])->name('admin.service-messages.edit');
    Route::patch('/admin/service-messages/{serviceMessage}', [ServiceMessageController::class, 'update'])->name('admin.service-messages.update');

    Route::get('/admin/audit-logs', [AuditLogController::class, 'index'])->name('admin.audit-logs.index');

    Route::get('/admin/settings', [SettingsController::class, 'index'])->name('admin.settings.index');
    Route::post('/admin/settings', [SettingsController::class, 'update'])->name('admin.settings.update');
});

Route::middleware(['auth', 'role:capitec_admin|city_reporter'])->group(function () {
    Route::get('/admin/two-factor', [TwoFactorController::class, 'show'])->name('admin.two-factor.challenge');
    Route::post('/admin/two-factor/send', [TwoFactorController::class, 'send'])->name('admin.two-factor.send');
    Route::post('/admin/two-factor/verify', [TwoFactorController::class, 'verify'])->name('admin.two-factor.verify');
});

Route::get('/tracking', [TrackingController::class, 'show'])->name('tracking.show');

require __DIR__.'/auth.php';
