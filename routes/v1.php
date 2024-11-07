<?php

use App\Http\Controllers\V1\Authenticate;
use App\Http\Controllers\V1\ConfirmAccount;
use App\Http\Controllers\V1\SignIn;
use App\Http\Controllers\V1\SignOut;
use App\Http\Controllers\V1\SignUp;
use Illuminate\Support\Facades\Route;

Route::group(['prefix' => 'v1'], function () {
    Route::post('/sign-up', SignUp::class);
    Route::post('/sign-in', SignIn::class)->middleware('throttle:authenticate')->name('login');
    Route::post('/confirm-account', ConfirmAccount::class)->middleware('throttle:authenticate');
    Route::post('/authenticate', Authenticate::class);
    Route::post('/sign-out', SignOut::class)->middleware('auth:sanctum')->name('sign-out');
});

Route::get('/mailable', function () {
    return new App\Mail\AuthenticateWithMagickLink(
        link: 'http://localhost',
        secretWord: 'HELLO-WORLD'
    );
});
