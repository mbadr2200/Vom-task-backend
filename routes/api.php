<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\ArticleController;


Route::middleware(['throttle:api'])->group(function () {

    // Article routes
    Route::prefix('articles')->group(function () {
        Route::get('/', [ArticleController::class, 'index'])->name('articles.index');
        Route::get('/sources', [ArticleController::class, 'sources'])->name('articles.sources');
        Route::get('/categories', [ArticleController::class, 'categories'])->name('articles.categories');
        Route::get('/{article}', [ArticleController::class, 'show'])->name('articles.show');
    });
});
