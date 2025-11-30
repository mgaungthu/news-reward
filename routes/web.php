<?php
use App\Http\Controllers\Admin\AdminAuthController;
use App\Http\Controllers\Admin\PostController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\SettingController;
use App\Http\Controllers\Admin\NotificationController;
use Illuminate\Support\Facades\Route;


Route::get('/admin/login', [AdminAuthController::class, 'showLoginForm'])->name('login');
Route::post('/admin/login', [AdminAuthController::class, 'login'])->name('admin.login');

// Admin Logout
Route::post('/admin/logout', [AdminAuthController::class, 'logout'])->name('admin.logout');

Route::middleware(['auth:web', 'admin'])->prefix('admin')->group(function () {
    Route::get('/dashboard', function() {
        return view('admin.dashboard');
    })->name('admin.dashboard');

    Route::resource('/posts', PostController::class);
    Route::resource('users', UserController::class);
    Route::resource('/settings', SettingController::class);

    Route::get('/account/delete', [UserController::class, 'deleteAccount'])->name('admin.account.delete');
    Route::delete('/account/destroy', [UserController::class, 'destroyAccount'])->name('admin.account.destroy');
    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::post('/send-notification', [NotificationController::class, 'sendToUser'])->name('notifications.send');
});