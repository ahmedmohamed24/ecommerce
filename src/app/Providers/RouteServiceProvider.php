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
     * The path to the "home" route for your application.
     *
     * This is used by Laravel authentication to redirect users after login.
     *
     * @var string
     */
    public const HOME = '/home';
    protected $namespaceV1 = 'App/Http/Controllers/V1/';

    /**
     * The controller namespace for the application.
     *
     * When present, controller route declarations will automatically be prefixed with this namespace.
     *
     * @var null|string
     */

    /**
     * Define your route model bindings, pattern filters, etc.
     */
    public function boot()
    {
        $this->configureRateLimiting();

        $this->routes(function () {
            Route::group(['middleware' => 'api_version', 'prefix' => '/api/'], function () {
                Route::group(['namespace' => $this->namespaceV1, 'prefix' => 'v1/'], function () {
                    Route::middleware('api')->group(base_path('routes/v1/web.php'));
                    Route::middleware('api')->group(base_path('routes/v1/admin.php'));
                    Route::middleware('api')->group(base_path('routes/v1/vendor.php'));
                });
            });
            Route::fallback(function () {
                return \response()->json(['message' => 'Not Found'], 404);
            });
        });
    }

    /**
     * Configure the rate limiters for the application.
     */
    protected function configureRateLimiting()
    {
        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(60)->by(optional($request->user())->id ?: $request->ip());
        });
    }
}
