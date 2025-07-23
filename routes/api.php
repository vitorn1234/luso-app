<?php

//use Illuminate\Http\Request;
use App\Http\Controllers\OrderController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    Route::post('/orders', [OrderController::class, 'createV1']);
});

Route::prefix('v2')->group(function () {
    Route::post('/orders', [OrderController::class, 'createV2']);
});
