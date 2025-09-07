<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;

class RouteServiceProvider extends ServiceProvider
{
    public const HOME = '/home';

    public function boot(): void
    {
        
        RateLimiter::for('api', function (Request $request) {
            $user = $request->user();

            if ($user) {
                if ($user->hasRole('admin')) {
                    return Limit::perMinute(200)->by($user->id);
                }

                if ($user->hasRole('stadium_owner')) {
                    return Limit::perMinute(100)->by($user->id);
                }

                if ($user->hasRole('player')) {
                    return Limit::perMinute(60)->by($user->id);
                }
            }


        });


        RateLimiter::for('auth', function (Request $request) {
            return [
                Limit::perMinute(5)->by($request->ip()),   // 5 محاولات بالدقيقة
                Limit::perHour(20)->by($request->ip()),    // 20 محاولة بالساعة
            ];
        });

        $this->routes(function () {
            Route::middleware('api')
                ->prefix('api')
                ->group(base_path('routes/api.php'));

            Route::middleware('web')
                ->group(base_path('routes/web.php'));
        });
    }
}
