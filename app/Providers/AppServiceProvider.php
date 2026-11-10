<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Vite;
use Illuminate\Support\ServiceProvider;

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
        Vite::prefetch(concurrency: 3);

        // Formulaires publics : quelques envois par IP et par formulaire (anti-spam).
        RateLimiter::for('site-forms', fn (Request $request) => Limit::perMinutes(
            config('antispam.form_decay_minutes'),
            config('antispam.form_attempts'),
        )->by($request->ip().'|'.$request->path())->response(
            fn () => back()->withErrors(['form' => __('form.too_many')]),
        ));
    }
}
