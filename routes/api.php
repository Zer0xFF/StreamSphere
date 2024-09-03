<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\XtreamCodeController;
use App\Http\Middleware\ProviderMiddleware;

Route::middleware([ProviderMiddleware::class])->group(function () {
    Route::get('/player_api.php', [XtreamCodeController::class, 'PlayerApi']);
    Route::get('/xmltv.php', [XtreamCodeController::class, 'getEPG']);
    Route::get('/{category}/{username}/{password}/{filename}', [XtreamCodeController::class, 'redirectToExternal'])
    ->where('category', 'live|movie|series');
    Route::get('/refreshCategories', [XtreamCodeController::class, 'refreshCategories']);
});

Route::get('/clearcache', [XtreamCodeController::class, 'clearCache']);
