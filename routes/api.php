<?php
use App\Http\Controllers\Api\PostController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\PostClaimController;





Route::get('/posts', [PostController::class, 'index']);
Route::get('/posts/{slug}', [PostController::class, 'show']);

Route::middleware('auth:api')->group(function () {
    Route::post('/posts/{post}/claim', [PostClaimController::class, 'claim']);
});