<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use App\Services\ModuleOverview;
use Illuminate\Support\Facades\View;
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
        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
        });

        View::composer(array_keys(ModuleOverview::VIEWS), function ($view) {
            try {
                $overview = app(ModuleOverview::class)->for($view->name());
            } catch (\Throwable $e) {
                report($e);
                $overview = null;
            }

            $view->with('moduleOverview', $overview);
        });
    }
}
