<?php

use App\Http\Controllers\PaymentController;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\WebhookController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::post('/payments/{providerSlug}', [PaymentController::class, 'processPayment']);
Route::post('/webhooks/superwalletz', [WebhookController::class, 'handle']);

Route::controller(TransactionController::class)->group(function () {
    Route::get('/transactions', 'index');
    Route::get('/transactions/{id}', 'show');
});
