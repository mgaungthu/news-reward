<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;

class RouteServiceProvider extends ServiceProvider
{
    /**
     * The path to your application's "home" route.
     *
     * Typically, users are redirected here after authentication.
     *
     * @var string
     */
    public const HOME = '/home';

    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // 🔥 GLOBAL API RATE LIMIT (60 requests/min per IP)
        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(60)->by($request->ip());
        });

        // 🔥 OTP Rate Limit (3 requests per minute)
        RateLimiter::for('otp', function (Request $request) {
            return Limit::perMinute(3)->by(
                $request->user()?->id ?? $request->ip()
            );
        });

        // 🔥 Reward Claim Rate Limit (5 per minute per user)
        RateLimiter::for('claim', function (Request $request) {
            return Limit::perMinute(5)->by(
                $request->user()?->id ?? $request->ip()
            );
        });

        // Load Routes
        $this->routes(function () {
            Route::middleware('api')
                ->prefix('api')
                ->group(base_path('routes/api.php'));

            Route::middleware('web')
                ->group(base_path('routes/web.php'));
        });
    }
}