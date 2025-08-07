<?php
use App\Http\Controllers\OrderController;
use App\Http\Middleware\DetectApiVersion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

//Route::prefix('v1')->group(function () {
//    Route::post('/orders', [OrderController::class, 'createV1']);
//});
//
//Route::prefix('v2')->group(function () {
//    Route::post('/orders', [OrderController::class, 'createV2']);
//});

//Route::any('{version}/order', function(Request $request, $version) {
    // Based on version, resolve controller
//    Route::post('', [OrderController::class, 'create']);
//    $controllerClass = match ($version) {
//        'v1' => \App\Http\Controllers\v1\OrderController::class,
//        'v2' => \App\Http\Controllers\v2\OrderController::class,
//        default => abort(404),
//    };
//
//    $method = match ($request->getMethod()) {
//        'POST' => "create",
//        default => abort(404),
//    };
//    $controllerInstance = app($controllerClass);
//    return app()->call([$controllerInstance, $method], ['request' => $request]);
//});
Route::group(['middleware' => DetectApiVersion::class], function () {
    Route::post('v2/order', [OrderController::class, 'create']);
    Route::post('v1/order', [OrderController::class, 'create']);
Route::get('v1/order', [OrderController::class, 'create']);
});

Route::post('testing/order/{version}', [\App\Http\Controllers\OrderController2::class, 'orderIntegrationTest']);
