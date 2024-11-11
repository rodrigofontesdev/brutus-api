<?php

use App\Http\Controllers\V1\Authenticate;
use App\Http\Controllers\V1\SignIn;
use App\Http\Controllers\V1\SignOut;
use App\Http\Controllers\V1\SignUp;
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
    });
