<?php

use App\Http\Controllers\V1\Authenticate;
use App\Http\Controllers\V1\CreateMeiCategory;
use App\Http\Controllers\V1\CreateReport;
use App\Http\Controllers\V1\DeleteMeiCategory;
use App\Http\Controllers\V1\DeleteReport;
use App\Http\Controllers\V1\DeleteSubscriber;
use App\Http\Controllers\V1\GetMeiCategories;
use App\Http\Controllers\V1\GetMeiCategory;
use App\Http\Controllers\V1\GetReport;
use App\Http\Controllers\V1\GetReports;
use App\Http\Controllers\V1\GetSubscriber;
use App\Http\Controllers\V1\Me;
use App\Http\Controllers\V1\SignIn;
use App\Http\Controllers\V1\SignOut;
use App\Http\Controllers\V1\SignUp;
use App\Http\Controllers\V1\UpdateReport;
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

        Route::get('/me', Me::class)
            ->middleware('auth:sanctum')
            ->name('me');

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

        Route::get('/mei-categories', GetMeiCategories::class)
            ->middleware('auth:sanctum')
            ->name('mei-categories.index');

        Route::get('/mei-categories/{id}', GetMeiCategory::class)
            ->middleware('auth:sanctum')
            ->name('mei-categories.show');

        Route::post('/mei-categories', CreateMeiCategory::class)
            ->middleware('auth:sanctum')
            ->name('mei-categories.create');

        Route::delete('/mei-categories/{id}', DeleteMeiCategory::class)
            ->middleware('auth:sanctum')
            ->name('mei-categories.delete');

        Route::get('/reports', GetReports::class)
            ->middleware('auth:sanctum')
            ->name('reports.index');

        Route::get('/reports/{id}', GetReport::class)
            ->middleware('auth:sanctum')
            ->name('reports.show');

        Route::post('/reports', CreateReport::class)
            ->middleware('auth:sanctum')
            ->name('reports.create');

        Route::put('/reports/{id}', UpdateReport::class)
            ->middleware('auth:sanctum')
            ->name('reports.update');

        Route::delete('/reports/{id}', DeleteReport::class)
            ->middleware('auth:sanctum')
            ->name('reports.delete');
    });
