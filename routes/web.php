<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\pdfController;

foreach (config('tenancy.central_domains') as $domain) {
    Route::domain($domain)->group(function () {
        Route::get('/', function () {
            return view('welcome');
        });
    });
}

Route::get('/pdf/{id}', [pdfController::class, 'generatedPDF'])->name('pdf.dwonload');

// Route::get('user/subscriptions/{record}/order', [OrderController::class, 'show'])->name('filament.user.resources.subscriptions.order');
