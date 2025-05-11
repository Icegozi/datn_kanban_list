<?php

use App\Http\Controllers\User\ColumnController;
use App\Http\Controllers\User\TaskController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\User\AttachmentController;
use App\Http\Controllers\User\DashboardController as UserDashboardController;
use App\Http\Controllers\User\BoardController;
use App\Http\Controllers\User\CommentController;

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

    // Dashboard riêng cho user thường
    Route::get('/user/dashboard', [UserDashboardController::class, 'index'])->name('user.dashboard');
    // Route::resource('boards', BoardController::class)->except([
    //     'index', 'create', 'edit' 
    // ])->middleware('auth');
    Route::resource('boards', BoardController::class)->except([
        'index',
        'create',
        'edit'
    ]);

    Route::post('/boards', [BoardController::class, 'store'])->name('boards.store');

    // Column Routes (Nested under boards)
    Route::post('/boards/{board}/columns', [ColumnController::class, 'store'])->name('columns.store');
    Route::put('/boards/{board}/columns/{column}', [ColumnController::class, 'update'])->name('columns.update');
    Route::delete('/boards/{board}/columns/{column}', [ColumnController::class, 'destroy'])->name('columns.destroy');
    Route::post('/boards/{board}/columns/reorder', [ColumnController::class, 'reorder'])->name('columns.reorder');


    // --- Task Routes ---
    Route::post('/columns/{column}/tasks', [TaskController::class, 'store'])->name('tasks.store');
    Route::get('/tasks/{task}', [TaskController::class, 'show'])->name('tasks.show');
    Route::put('/tasks/{task}', [TaskController::class, 'update'])->name('tasks.update');
    Route::delete('/tasks/{task}', [TaskController::class, 'destroy'])->name('tasks.destroy');
    Route::post('/tasks/update-position', [TaskController::class, 'updatePosition'])->name('tasks.updatePosition');
    Route::get('/tasks/{task}/details', [TaskController::class, 'showDetailsPage'])->name('tasks.showDetailsPage');

    // comment
    Route::post('/tasks/{task}/comments', [CommentController::class, 'store'])->name('comments.store');
    // Route::put('/tasks/{task}/comments/{commentId}', [CommentController::class, 'update'])->name('comments.update');
    Route::delete('/tasks/{task}/comments/{commentId}', [CommentController::class, 'destroy'])->name('comments.destroy');

    // attackment

    // Attachment routes
    Route::get('/tasks/{task}/attachments', [AttachmentController::class, 'index'])->name('attachments.index'); 
    Route::post('/tasks/{task}/attachments', [AttachmentController::class, 'store'])->name('attachments.store');
    Route::delete('/attachments/{attachment}', [AttachmentController::class, 'destroy'])->name('attachments.destroy');

    // ==== ROUTE ĐẶC BIỆT CHO ADMIN ====
    Route::middleware('is_admin')->group(function () {
        Route::get('/admin/dashboard', [AdminDashboardController::class, 'index'])->name('admin.dashboard');

        // Các chức năng nâng cao khác dành riêng cho admin có thể đặt ở đây
    });
});
