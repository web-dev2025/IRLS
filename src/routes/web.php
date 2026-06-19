<?php

use App\Http\Controllers\Api\DictionaryController;
use App\Http\Controllers\Api\NoteController;
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
Route::get('chapters/{chapter}/read', [ChapterController::class, 'read'])->name('chapters.read');

Route::prefix('api')->name('api.')->group(function () {
    Route::post('notes', [NoteController::class, 'store'])->name('notes.store');
    Route::delete('notes/{note}', [NoteController::class, 'destroy'])->name('notes.destroy');
    Route::get('dictionary/{word}', [DictionaryController::class, 'lookup'])->name('dictionary.lookup');
});
