<?php

use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ChapterController;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => redirect()->route('categories.index'));

Route::resource('categories', CategoryController::class);

Route::prefix('categories/{category}/chapters')->name('categories.chapters.')->group(function () {
    Route::get('create', [ChapterController::class, 'create'])->name('create');
    Route::post('/', [ChapterController::class, 'store'])->name('store');
    Route::delete('{chapter}', [ChapterController::class, 'destroy'])->name('destroy');
});

Route::get('chapters/{chapter}/status', [ChapterController::class, 'status'])->name('chapters.status');
