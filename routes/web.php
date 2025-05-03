<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\WordleController;
use App\Http\Controllers\SpeedClickController;

Route::get('/wordle', [WordleController::class, 'index'])->name('wordle.index');
Route::post('/wordle/try', [WordleController::class, 'check'])->name('wordle.check');
Route::get('/speedclick', [SpeedClickController::class, 'index'])->name('speedclick.index');


Route::get('/', function () {
    return view('home');
});
