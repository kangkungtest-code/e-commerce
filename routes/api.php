<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// API publik versi 1: /api/v1/...
Route::prefix('v1')->group(function () {
    Route::get('/ping', fn () => ['status' => 'ok']);

    Route::middleware('auth:sanctum')->get('/me', fn (Request $request) => $request->user());
});
