<?php

use App\Http\Controllers\V1\Authenticate;
use App\Http\Controllers\V1\DeleteSubscriber;
use App\Http\Controllers\V1\GetReports;
use App\Http\Controllers\V1\GetSubscriber;
use App\Http\Controllers\V1\SignIn;
use App\Http\Controllers\V1\SignOut;
use App\Http\Controllers\V1\SignUp;
use App\Http\Controllers\V1\UpdateSubscriber;
use Illuminate\Support\Facades\Route;

Route::name('v1.')
    ->prefix('v1')
    ->group(function () {
        Route::post('/sign-up', SignUp::class)
            ->name('sign-up');

        Route::post('/sign-in', SignIn::class)
            ->middleware('throttle:authenticate')
            ->name('sign-in');

        Route::post('/authenticate', Authenticate::class)
            ->name('authenticate');

        Route::post('/sign-out', SignOut::class)
            ->middleware('auth:sanctum')
            ->name('sign-out');

        Route::get('/subscribers/{id}', GetSubscriber::class)
            ->middleware('auth:sanctum')
            ->name('subscribers.show');

        Route::patch('/subscribers/{id}', UpdateSubscriber::class)
            ->middleware('auth:sanctum')
            ->name('subscribers.update');

        Route::delete('/subscribers/{id}', DeleteSubscriber::class)
            ->middleware('auth:sanctum')
            ->name('subscribers.delete');

        Route::get('/reports', GetReports::class)
            ->middleware('auth:sanctum')
            ->name('reports.index');
    });
