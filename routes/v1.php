<?php

// use App\Http\Controllers\V1\Authenticate;
use App\Http\Controllers\V1\ConfirmAccount;
use App\Http\Controllers\V1\SignUp;
use App\Http\Controllers\V1\SignIn;
use Illuminate\Support\Facades\Route;

Route::group(['prefix' => 'v1'], function () {
    Route::post('/sign-up', SignUp::class);
    Route::post('/sign-in', SignIn::class)->middleware('throttle:authenticate');
    Route::post('/confirm-account', ConfirmAccount::class)->middleware('throttle:authenticate');
    // Route::post('/authenticate', Authenticate::class);
});
