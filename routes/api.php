<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\PostController;
use App\Http\Controllers\Api\PostClaimController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\SettingController;
use App\Http\Controllers\Api\NotificationController;



Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::get('/check', function () {
    return response()->json([
        'status' => 'ok',
        'message' => 'API is running successfully 🚀'
    ]);
});


Route::get('/posts', [PostController::class, 'index']);
Route::get('/posts/{id}', [PostController::class, 'show']);
Route::get('/vip-posts', [PostController::class, 'vipPosts']);
Route::get('/settings', [SettingController::class, 'index']);

Route::middleware('auth:api')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::post('/posts/{post}/claim', [PostClaimController::class, 'claim']);
    Route::post('/posts/{id}/buy', [PostController::class, 'buy']);
    Route::get('/me', [AuthController::class, 'me']);
    Route::put('/user/update', [AuthController::class, 'updateProfile']);
    Route::get('/user/vip-posts', [PostController::class, 'purchasedVipPosts']);
    Route::post('/user/reset-claims', [PostController::class, 'resetUserClaims']);
    Route::post('/save-push-token', [NotificationController::class, 'savePushToken']);
});