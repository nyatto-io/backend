<?php

use App\Http\Controllers\AnimeController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ChapterController;
use App\Http\Controllers\ConfigController;
use App\Http\Controllers\FavoriteController;
use App\Http\Controllers\FileController;
use App\Http\Controllers\MangaController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\StatisticsController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::prefix('/v1')->group(function () {
    Route::prefix('/auth')->group(function () {
        Route::post('/resend-email', [AuthController::class, 'resendVerificationEmail']);
        Route::post('/login', [AuthController::class, 'login']);
        Route::post('/register', [AuthController::class, 'register']);
        Route::middleware(['auth:sanctum', 'verified'])->group(function () {
            Route::get('/check', [AuthController::class, 'check']);
            Route::post('/logout', [AuthController::class, 'logout']);
            Route::post('/profile', [AuthController::class, 'profile']);
            Route::post('/picture', [AuthController::class, 'picture']);
            Route::apiResource('/settings', SettingController::class)->only(['index', 'store']);
            Route::post('/settings/bulk', [SettingController::class, 'storeBulk']);
        });
        Route::post('/forgot-password', [AuthController::class, 'sendForgotPasswordEmail'])->name('password.email');
        Route::post('/reset-password', [AuthController::class, 'resetPassword'])->name('password.update');
    });

    Route::get('/file/{file}', FileController::class);

    Route::middleware(['auth:sanctum', 'verified'])->group(function () {
        Route::prefix('/manga')->group(function () {
            Route::any('/webtoons', [MangaController::class, 'commandWebtoon']);
            Route::any('/mangakakalot', [MangaController::class, 'commandMangakakalot']);
        });
        Route::apiResource('manga', MangaController::class)->except(['store', 'update']);
        Route::apiResource('manga.chapters', ChapterController::class)->only(['index', 'show']);

        Route::prefix('/anime')->group(function () {
            Route::any('/gogoanime', [AnimeController::class, 'commandGogoanime']);
        });
        Route::apiResource('/anime', AnimeController::class)->except(['store', 'update']);

        Route::apiResource('favorites', FavoriteController::class)->except('update');

        Route::prefix('/config')->group(function () {
            Route::get('/drivers', [ConfigController::class, 'drivers']);
        });

        Route::prefix('/statistics')->group(function () {
            Route::get('/animes-and-mangas', [StatisticsController::class, 'cachedAnimesAndMangas']);
            Route::get('/favorites', [StatisticsController::class, 'favorites']);
        });
    });
});
