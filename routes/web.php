<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\User\DashboardController as UserDashboardController;
use App\Http\Controllers\User\BoardController;

// Trang chủ
Route::get('/', function () {
    return view('welcome');
})->name('welcome');

// ==== AUTHENTICATION ====
// Đăng xuất
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

// Các route cho khách chưa đăng nhập
Route::middleware('guest')->group(function () {
    // Đăng ký
    Route::get('/register', [RegisterController::class, 'showRegistrationForm'])->name('register.form');
    Route::post('/register', [RegisterController::class, 'register'])->name('register');

    // Đăng nhập
    Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login.form');
    Route::post('/login', [LoginController::class, 'login'])->name('login');
});

// ==== ROUTE CHUNG CHO USER & ADMIN ====
Route::middleware(['auth'])->group(function () {

    // Dashboard chung, tự điều hướng theo role
    Route::get('/dashboard', function () {
        return auth()->user()->is_admin
            ? redirect()->route('admin.dashboard')
            : redirect()->route('user.dashboard');
    })->name('dashboard');

    // Board (dành cho cả user và admin)
    Route::resource('boards', BoardController::class);

    // Dashboard riêng cho user thường
    Route::get('/user/dashboard', [UserDashboardController::class, 'index'])->name('user.dashboard');

    // ==== ROUTE ĐẶC BIỆT CHO ADMIN ====
    Route::middleware('is_admin')->group(function () {
        Route::get('/admin/dashboard', [AdminDashboardController::class, 'index'])->name('admin.dashboard');

        // Các chức năng nâng cao khác dành riêng cho admin có thể đặt ở đây
    });
});
