<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\FeatureController;
use App\Http\Controllers\Api\CollectionController;

Route::middleware('api.key')->group(function () {
    Route::group(['prefix' => 'features'], function () {
        Route::get('/', [FeatureController::class, 'index']);
        Route::post('/', [FeatureController::class, 'store']);
        Route::get('/trashed', [FeatureController::class, 'trashed']);
        Route::get('/{feature}', [FeatureController::class, 'show']);
        Route::put('/{feature}', [FeatureController::class, 'update']);
        Route::delete('/{feature}', [FeatureController::class, 'softDelete']);
        Route::post('/{feature}/restore', [FeatureController::class, 'restore'])->withTrashed();
        Route::delete('/{feature}/permanent', [FeatureController::class, 'permanentlyDelete'])->withTrashed();
    });

    Route::group(['prefix' => 'collections'], function () {
        Route::get('/', [CollectionController::class, 'index']);
        Route::get('/{collection}', [CollectionController::class, 'show']);
        Route::post('/', [CollectionController::class, 'store']);
        Route::get('/trashed', [CollectionController::class, 'trashed']);
        Route::put('/{collection}', [CollectionController::class, 'update']);
        Route::delete('/{collection}', [CollectionController::class, 'softDelete']);
        Route::post('/{collection}/restore', [CollectionController::class, 'restore'])->withTrashed();
        Route::delete('/{collection}/permanent', [CollectionController::class, 'permanentlyDelete'])->withTrashed();
    });
});
