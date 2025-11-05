<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Laravel\Passport\Passport;

class AuthServiceProvider extends ServiceProvider
{
    protected $policies = [
        // 'App\Models\Model' => 'App\Policies\ModelPolicy',
        \App\Models\Post::class => \App\Policies\PostPolicy::class,
    ];

    public function boot(): void
    {
        $this->registerPolicies();

        // 👇 No need for Passport::routes() in Laravel 11+
        Passport::tokensExpireIn(now()->addDays(7));
        Passport::refreshTokensExpireIn(now()->addDays(30));
    }
}