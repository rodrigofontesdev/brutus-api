<?php

namespace App\Providers;

use App\Exceptions\V1\RateLimitException;
use App\Notifications\QueueHasLongWaitTime;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Queue\Events\QueueBusy;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\RateLimiter;
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
                    throw new RateLimitException(headers: $headers);
                });
        });

        Event::listen(function (QueueBusy $event) {
            Notification::route('mail', config('mail.admin'))
                    ->notify(new QueueHasLongWaitTime($event));
        });
    }
}
