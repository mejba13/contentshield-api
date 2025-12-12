<?php

use App\Http\Controllers\Webhooks\LemonSqueezyController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Webhook Routes
|--------------------------------------------------------------------------
|
| Payment provider webhook handlers.
| These routes are public but verified via signatures.
|
*/

// LemonSqueezy webhooks
Route::post('/lemonsqueezy', [LemonSqueezyController::class, 'handle'])
    ->name('webhooks.lemonsqueezy');

// Paddle webhooks (future)
// Route::post('/paddle', [PaddleController::class, 'handle'])
//     ->name('webhooks.paddle');

// Stripe webhooks (future)
// Route::post('/stripe', [StripeController::class, 'handle'])
//     ->name('webhooks.stripe');
