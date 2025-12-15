<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Validator;

class AppServiceProvider extends ServiceProvider
{
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
        // Some validation rules (e.g., "username") may be referenced by views/controllers.
        // Provide a simple fallback: treat "username" as an email-style value.
        Validator::extend('username', function ($attribute, $value) {
            return filter_var($value, FILTER_VALIDATE_EMAIL) !== false;
        }, 'The :attribute must be a valid email.');
    }
}

