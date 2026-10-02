<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\LeaveRequestController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

// Redirect Root to Login or Presensi/Dashboard
Route::get('/', function () {
    if (auth()->check()) {
        return auth()->user()->isAdmin()
            ? redirect()->route('admin.dashboard')
            : redirect()->route('presensi.index');
    }
    return redirect()->route('login');
});

// Guest Routes
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);
    Route::get('/forgot-password', [AuthController::class, 'showForgotPassword'])->name('forgot-password');
    Route::post('/forgot-password', [AuthController::class, 'resetPasswordSelf'])->name('forgot-password.store');
});

// Authenticated Routes
Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    // Presensi Routes (Peserta Magang & Admin)
    Route::get('/presensi', [AttendanceController::class, 'index'])->name('presensi.index');
    Route::get('/presensi/office-info', [AttendanceController::class, 'officeInfo'])->name('presensi.office-info');
    Route::post('/presensi/checkin', [AttendanceController::class, 'storeCheckIn'])->name('presensi.checkin');
    Route::post('/presensi/checkout', [AttendanceController::class, 'storeCheckOut'])->name('presensi.checkout');

    // Modul Pengajuan Izin / Sakit / Cuti (Peserta Magang)
    Route::get('/presensi/izin', [LeaveRequestController::class, 'userIndex'])->name('presensi.leave.index');
    Route::post('/presensi/izin', [LeaveRequestController::class, 'store'])->name('presensi.leave.store');

    // Modul Profil Pengguna
    Route::get('/profile', [ProfileController::class, 'show'])->name('profile.show');
    Route::post('/profile', [ProfileController::class, 'updateProfile'])->name('profile.update');
    Route::post('/profile/password', [ProfileController::class, 'updatePassword'])->name('profile.password');

    // Admin Routes
    Route::middleware('admin')->prefix('admin')->name('admin.')->group(function () {
        Route::get('/', function () {
            return redirect()->route('admin.dashboard');
        });
        Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('dashboard');
        Route::get('/attendances', [AdminController::class, 'attendances'])->name('attendances');
        Route::get('/export-csv', [AdminController::class, 'exportCsv'])->name('export-csv');
        Route::get('/office', [AdminController::class, 'officeLocation'])->name('office');
        Route::post('/office', [AdminController::class, 'officeLocationUpdate'])->name('office.update');
        Route::get('/users', [AdminController::class, 'users'])->name('users');
        Route::post('/users', [AdminController::class, 'userStore'])->name('users.store');
        Route::post('/users/{id}/update', [AdminController::class, 'userUpdate'])->name('users.update');
        Route::post('/users/{id}/reset-password', [AdminController::class, 'userResetPassword'])->name('users.reset-password');
        Route::post('/users/{id}/toggle', [AdminController::class, 'userToggleStatus'])->name('users.toggle');
        Route::get('/users/{id}/report', [AdminController::class, 'userReport'])->name('users.report');

        // Kelola Pengajuan Izin / Sakit / Cuti (Admin)
        Route::get('/leave-requests', [LeaveRequestController::class, 'adminIndex'])->name('leave.index');
        Route::post('/leave-requests/{id}/approve', [LeaveRequestController::class, 'approve'])->name('leave.approve');
        Route::post('/leave-requests/{id}/reject', [LeaveRequestController::class, 'reject'])->name('leave.reject');
    });
});
