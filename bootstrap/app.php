<?php

use App\Exceptions\V1\AuthenticationException;
use App\Exceptions\V1\AuthorizationException;
use App\Exceptions\V1\MethodNotAllowedException;
use App\Exceptions\V1\NotFoundException;
use App\Http\Middleware\EnsureLogHasContext;
use Illuminate\Auth\AuthenticationException as BuiltInAuthenticationException;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Session\Middleware\StartSession;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        api: [
            __DIR__.'/../routes/api/v1.php',
        ],
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withBroadcasting(
        __DIR__.'/../routes/channels.php',
        ['prefix' => 'api', 'middleware' => ['api', 'auth:sanctum']],
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->statefulApi();
        $middleware->append([
            EnsureLogHasContext::class,
        ]);
        $middleware->api(append: [
            StartSession::class,
            EnsureLogHasContext::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        $exceptions->render(function (BuiltInAuthenticationException $error, Request $request) {
            throw_if(
                $request->is('api/*'),
                AuthenticationException::class,
                $request->route()->getControllerClass().':: Unauthenticated user attempted to access a protected route.'
            );
        })
        ->render(function (MethodNotAllowedHttpException $error, Request $request) {
            throw_if(
                $request->is('api/*'),
                MethodNotAllowedException::class,
                headers: $error->getHeaders()
            );
        })
        ->render(function (AccessDeniedHttpException $error, Request $request) {
            throw_if(
                $request->is('api/*'),
                AuthorizationException::class,
                message: $request->route()->getControllerClass().':: User don\'t have sufficient permissions to perform this action.'
            );
        })
        ->render(function (NotFoundHttpException $error, Request $request) {
            throw_if(
                $request->is('api/*'),
                NotFoundException::class,
                message: $request->route()->getControllerClass().':: Requested resource could not be found.'
            );
        })
        ->throttle(function (Throwable $error) {
            if ($error instanceof AuthenticationException) {
                return Limit::perMinute(100);
            }
        });
    })->create();
