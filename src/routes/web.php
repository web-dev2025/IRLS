<?php

use App\Http\Controllers\CategoryController;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => redirect()->route('categories.index'));

Route::resource('categories', CategoryController::class);
