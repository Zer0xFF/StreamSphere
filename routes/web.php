<?php

use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::view('dashboard', 'dashboard')->name('dashboard');
    Route::view('profile', 'profile')->name('profile');

    Route::view('/providers', 'providers')->name('providers');
    Route::view('/devices', 'devices')->name('devices');
    Route::view('/filters', 'filters')->name('filters');
    Route::view('/patterns', 'patterns')->name('patterns');
});

use App\Http\Controllers\StringTestController;
Route::get('/strtest', [StringTestController::class, 'benchmark'])->name('strtest');

require __DIR__.'/auth.php';
