<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void {}

    public function boot(): void
    {
        RateLimiter::for('authenticate', function (Request $request) {
            return Limit::perHour(5)
                ->by($request->ip())
                ->response(function (Request $request, array $headers) {
                    return Response::json(
                        [
                            'type' => 'INVALID_REQUEST_ERROR',
                            'code' => 429,
                            'message' => 'You have reached the maximum number of requests per hour. Please wait a while to continue.',
                            'path' => '/' . $request->path(),
                            'timestamp' => now(),
                        ],
                        429,
                        $headers
                    );
                });
        });
    }
}
