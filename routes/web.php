<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\XtreamBrowserController;

Route::view('/', 'welcome');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::view('dashboard', 'dashboard')->name('dashboard');
    Route::view('profile', 'profile')->name('profile');

    Route::view('/providers', 'providers')->name('providers');
    Route::view('/devices', 'devices')->name('devices');
    Route::view('/filters', 'filters')->name('filters');
    Route::view('/patterns', 'patterns')->name('patterns');
    Route::get('/xtream-browser', [XtreamBrowserController::class, 'index'])->name('xtream-browser');
    Route::get('/xtream-browser/categories', [XtreamBrowserController::class, 'categories'])->name('xtream-browser.categories');
    Route::get('/xtream-browser/search', [XtreamBrowserController::class, 'search'])->name('xtream-browser.search');
});

use App\Http\Controllers\StringTestController;
Route::get('/strtest', [StringTestController::class, 'benchmark'])->name('strtest');

require __DIR__.'/auth.php';
