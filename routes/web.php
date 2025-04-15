<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\WordleController;

Route::get('/wordle', [WordleController::class, 'index'])->name('wordle.index');
Route::post('/wordle/try', [WordleController::class, 'check'])->name('wordle.check');
Route::get('/', function () {
    return view('home');
});
