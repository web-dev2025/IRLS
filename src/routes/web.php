<?php

use App\Http\Controllers\Api\DictionaryController as ApiDictionaryController;
use App\Http\Controllers\Api\NoteController;
use App\Http\Controllers\Api\OcrController;
use App\Http\Controllers\Api\TranslateController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ChapterController;
use App\Http\Controllers\DictionaryController;
use App\Http\Controllers\QuizController;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => view('welcome'))->name('home');

Route::resource('categories', CategoryController::class);

Route::prefix('categories/{category}/chapters')->name('categories.chapters.')->group(function () {
    Route::get('create', [ChapterController::class, 'create'])->name('create');
    Route::post('/', [ChapterController::class, 'store'])->name('store');
    Route::delete('{chapter}', [ChapterController::class, 'destroy'])->name('destroy');
    Route::get('sort', [ChapterController::class, 'sort'])->name('sort');
    Route::patch('reorder', [ChapterController::class, 'reorder'])->name('reorder');
});

Route::get('chapters/{chapter}/status', [ChapterController::class, 'status'])->name('chapters.status');
Route::get('chapters/{chapter}/read', [ChapterController::class, 'read'])->name('chapters.read');

Route::get('dictionary', [DictionaryController::class, 'index'])->name('dictionary.index');
Route::get('dictionary/export', [DictionaryController::class, 'export'])->name('dictionary.export');
Route::get('quiz', [QuizController::class, 'index'])->name('quiz.index');

Route::prefix('api')->name('api.')->group(function () {
    Route::post('notes', [NoteController::class, 'store'])->name('notes.store');
    Route::delete('notes/{note}', [NoteController::class, 'destroy'])->name('notes.destroy');
    Route::patch('notes/{note}/learned', [NoteController::class, 'toggleLearned'])->name('notes.toggleLearned');
    Route::get('dictionary/{word}', [ApiDictionaryController::class, 'lookup'])->name('dictionary.lookup');
    Route::post('ocr', [OcrController::class, 'recognize'])->name('ocr');
    Route::get('translate/{text}', [TranslateController::class, 'translate'])->name('translate');
});
