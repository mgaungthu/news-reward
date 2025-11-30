<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\PostController;
use App\Http\Controllers\Api\PostClaimController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\SettingController;
use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\Api\IAPController;
use App\Http\Controllers\Api\resetPasswordController;

Route::prefix('v1')->group(function () {

Route::middleware(['throttle:api'])->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/forgot-password', [resetPasswordController::class, 'forgotPassword'])->middleware('throttle:otp');
    Route::post('/verify-reset-otp', [resetPasswordController::class, 'verifyResetOtp']);
    Route::post('/reset-password', [resetPasswordController::class, 'resetPassword']);

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
    Route::post('/user/change-password', [AuthController::class, 'changePassword']);

    Route::post('/verify-email', [AuthController::class, 'verifyEmail']);
    Route::post('/resend-otp', [AuthController::class, 'resendOtp'])->middleware('throttle:otp');;

    Route::middleware(['auth:api', 'device', 'verify'])->group(function () {
    
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::post('/posts/{post}/claim', [PostClaimController::class, 'claim'])->middleware('throttle:claim');;
        Route::post('/posts/{id}/buy', [PostController::class, 'buy']);
        Route::get('/me', [AuthController::class, 'me']);
        Route::put('/user/update', [AuthController::class, 'updateProfile']);
        Route::get('/user/vip-posts', [PostController::class, 'purchasedVipPosts']);
        Route::post('/user/reset-claims', [PostController::class, 'resetUserClaims']);
        Route::post('/save-push-token', [NotificationController::class, 'savePushToken']);
        
        Route::delete('/user/delete', [AuthController::class, 'deleteAccount']);

        Route::post('/iap/validate', [IAPController::class, 'validatePurchase']);
        Route::get('/iap/history', [IAPController::class, 'history']);
    });
    
});

});