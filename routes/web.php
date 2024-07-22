<?php

use Illuminate\Support\Facades\Route;
use App\Filament\User\Resources\SubscriptionResource;
use App\Filament\User\Resources\SubscriptionResource\Pages\Order;
use App\Http\Controllers\OrderController;

foreach (config('tenancy.central_domains') as $domain) {
    Route::domain($domain)->group(function () {
        Route::get('/', function () {
            return view('welcome');
        });
    });
}

