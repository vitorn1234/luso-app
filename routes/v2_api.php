<?php

use App\Http\Controllers\v2\OrderController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::prefix('v2')->group(function () {

    Route::post('/order', [OrderController::class, 'create']);

    Route::get('/order/{id}', function (Request $request) {
        return $request->route()->parameter('id');
    });
});
