<?php

use App\Http\Controllers\Api\V1\MarketplaceApiController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->middleware('throttle:60,1')->group(function ()
{
    Route::post('/auth/login', [MarketplaceApiController::class, 'login'])->middleware('throttle:10,1');
    Route::get('/products', [MarketplaceApiController::class, 'products']);
    Route::get('/products/{slug}', [MarketplaceApiController::class, 'showProduct']);
    Route::get('/categories', [MarketplaceApiController::class, 'categories']);

    Route::middleware('auth:sanctum')->group(function ()
    {
        Route::get('/cart', [MarketplaceApiController::class, 'getCart']);
        Route::post('/cart', [MarketplaceApiController::class, 'addToCart']);
        Route::get('/orders', [MarketplaceApiController::class, 'orders']);
    });
});
