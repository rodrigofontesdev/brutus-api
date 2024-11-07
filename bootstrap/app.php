<?php

use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        api: [
            __DIR__.'/../routes/v1.php',
        ],
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->statefulApi();
        $middleware->group('api', [
            Illuminate\Session\Middleware\StartSession::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        $exceptions->render(function (AuthenticationException $error, Request $request) {
            if ($request->is('api/*')) {
                return response()->json([
                    'type' => 'PERMISSION_ERROR',
                    'code' => 'authentication_required',
                    'message' => 'You need to be authenticated to access this endpoint. Please login to continue.',
                    'path' => '/'.$request->path(),
                    'timestamp' => now()->toDateTimeString(),
                ], 401);
            }
        });
    })->create();
