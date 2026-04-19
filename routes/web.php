<?php

use Illuminate\Support\Facades\Route;

Route::view('/', 'api-docs')->name('api.docs');
Route::view('/features', 'api-docs');
Route::view('/collections', 'api-docs');
Route::get('/postman-collection', function () {
    return response()->download(base_path('docs/postman/memorial-app.postman_collection.json'));
});

Route::fallback(function () {
    if (request()->is('api/*')) {
        abort(404);
    }

    return view('api-docs');
});
