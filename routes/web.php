<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;

Route::view('/', 'api-docs')->name('api.docs');
Route::view('/features', 'api-docs');
Route::view('/collections', 'api-docs');
Route::get('/postman-collection', function () {
    return response()->download(base_path('docs/postman/memorial-app.postman_collection.json'));
});
Route::get('/storage/{path}', function (string $path) {
    abort_unless(Storage::disk('public')->exists($path), 404);

    return Storage::disk('public')->response($path);
})->where('path', '.*')->name('storage.public');

Route::fallback(function () {
    if (request()->is('api/*')) {
        abort(404);
    }

    return view('api-docs');
});
