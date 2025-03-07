<?php

use Kobalt\LaravelMoneybird\Http\Controllers\OAuthController;
use Illuminate\Support\Facades\Route;

Route::middleware(['web'])->group(function () {
    Route::prefix('moneybird')->name('moneybird.')->group(function () {
        Route::get('connect', [OAuthController::class, 'redirect'])->name('connect');
        Route::get('callback', [OAuthController::class, 'callback'])->name('callback');
        Route::get('disconnect', [OAuthController::class, 'disconnect'])->name('disconnect');
    });
});