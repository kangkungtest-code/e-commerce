<?php

use App\Http\Controllers\Toko\KatalogController;
use App\Http\Controllers\Toko\PreferensiController;
use Illuminate\Support\Facades\Route;

Route::get('/', [KatalogController::class, 'home'])->name('home');
Route::get('/produk', [KatalogController::class, 'index'])->name('produk.index');
Route::get('/produk/{product}', [KatalogController::class, 'show'])->name('produk.show');
Route::get('/faq', [KatalogController::class, 'faq'])->name('faq');

Route::post('/preferensi', [PreferensiController::class, 'update'])
    ->middleware('throttle:30,1')
    ->name('preferensi');
