<?php

namespace App\Providers;

use App\Notifications\QueueHasLongWaitTime;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Queue\Events\QueueBusy;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
    }

    public function boot(): void
    {
        RateLimiter::for('authenticate', function (Request $request) {
            return Limit::perHour(5)
                ->by($request->ip())
                ->response(function (Request $request, array $headers) {
                    return Response::json(
                        [
                            'type' => 'RATE_LIMIT_ERROR',
                            'code' => 'rate_limit',
                            'message' => 'You have reached the maximum number of requests per hour. Please wait a while to continue.',
                            'path' => '/'.$request->path(),
                            'timestamp' => now()->toDateTimeString(),
                        ],
                        429,
                        $headers
                    );
                });
        });

        Event::listen(function (QueueBusy $event) {
            Notification::route('mail', config('mail.admin'))
                    ->notify(new QueueHasLongWaitTime($event));
        });
    }
}
