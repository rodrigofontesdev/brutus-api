<?php

use App\Exceptions\V1\AuthenticationException;
use Illuminate\Auth\AuthenticationException as AuthenticationExceptionIlluminate;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        api: [
            __DIR__.'/../routes/api/v1.php',
        ],
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->statefulApi();
        $middleware->api(append: [
            Illuminate\Session\Middleware\StartSession::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        $exceptions->render(function (AuthenticationExceptionIlluminate $error, Request $request) {
            throw_if(
                $request->is('api/*'),
                AuthenticationException::class,
                self::class.':: Unauthorized user attempted to access a protected route.'
            );
        })->throttle(function (Throwable $error) {
            if ($error instanceof AuthenticationException) {
                return Limit::perMinute(100);
            }
        });
    })->create();
