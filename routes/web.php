<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\OrderController;

foreach (config('tenancy.central_domains') as $domain) {
    Route::domain($domain)->group(function () {
        Route::get('/', function () {
            return view('welcome');
        });
    });
}

// Route::get('user/subscriptions/{record}/order', [OrderController::class, 'show'])->name('filament.user.resources.subscriptions.order');
