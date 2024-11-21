<?php

use App\Exceptions\V1\AuthenticationException;
use App\Http\Middleware\EnsureLogHasContext;
use Illuminate\Auth\AuthenticationException as BuiltInAuthenticationException;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Session\Middleware\StartSession;

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
        $middleware->append([
            EnsureLogHasContext::class,
        ]);
        $middleware->api(append: [
            StartSession::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        $exceptions->render(function (BuiltInAuthenticationException $error, Request $request) {
            throw_if($request->is('api/*'), AuthenticationException::class);
        })
        ->throttle(function (Throwable $error) {
            if ($error instanceof AuthenticationException) {
                return Limit::perMinute(100);
            }
        });
    })->create();
