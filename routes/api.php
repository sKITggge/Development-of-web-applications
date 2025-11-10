<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PostController;
use App\Http\Controllers\Api\RssImportController;
use App\Http\Controllers\Api\SourceController;
use App\Http\Controllers\Api\ModeratorSourceController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\ProfileController;
use App\Http\Controllers\Api\AuthController;

use App\Http\Middleware\AuthenticateWithToken;
use App\Http\Middleware\ModeratorAccess;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::middleware([AuthenticateWithToken::class])->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    
    Route::get('/profile', [ProfileController::class, 'show']);
    
    Route::controller(ProfileController::class)->prefix('profile/categories')->group(function () {
        Route::get('/', 'getTrackedCategories');
        Route::put('/', 'updateTrackedCategories');
    });
    
    Route::controller(ProfileController::class)->prefix('profile/sources')->group(function () {
        Route::get('/', 'getTrackedSources');
        Route::put('/', 'updateTrackedSources');
    });

    Route::middleware([ModeratorAccess::class])->apiResource('moderateSources', ModeratorSourceController::class);
});

Route::post('/rss/import', [RssImportController::class, 'import']);

Route::apiResource('posts', PostController::class);

Route::apiResource('sources', SourceController::class);

Route::apiResource('categories', CategoryController::class)->except(['destroy']);