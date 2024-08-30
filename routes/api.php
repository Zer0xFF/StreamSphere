<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\XtreamCodeController;
use App\Http\Middleware\ProviderMiddleware;

Route::middleware([ProviderMiddleware::class])->group(function () {
    Route::get('/player_api.php', [XtreamCodeController::class, 'PlayerApi']);
    Route::get('/xmltv.php', [XtreamCodeController::class, 'getEPG']);
    Route::get('/live/{username}/{password}/{filename}', [XtreamCodeController::class, 'redirectToExternal']);
});

Route::get('/clearcache', [XtreamCodeController::class, 'clearCache']);
Route::get('/refreshfilters', [XtreamCodeController::class, 'refreshFilters']);
