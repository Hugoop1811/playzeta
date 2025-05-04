<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\WordleController;
use App\Http\Controllers\BattleshipController;  
use App\Models\BattleshipGame;

/*
|--------------------------------------------------------------------------
| Web Routes - PlayZeta
|--------------------------------------------------------------------------
*/

// Página principal
Route::get('/', function () {
    return view('home');
})->name('home');

// Selector de juegos
Route::get('/games', function () {
    return view('games.index');
})->name('games.index');

// Wordle y sus variantes
Route::get('/wordle', [WordleController::class, 'index'])->name('wordle.index');
Route::post('/wordle/check', [WordleController::class, 'check'])->name('wordle.check');

Route::get('/wordle/advanced', function () {
    return view('games.wordle_advanced');
})->name('wordle.advanced');

Route::get('/wordle/nn-ready', function () {
    return view('games.wordle_nn_ready');
})->name('wordle.nn_ready');

// Dashboard (usuarios logueados)
Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

// Perfil de usuario
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// Rutas de battleship
Route::middleware('auth')->prefix('battleship')->name('battleship.')->group(function () {
    // Panel principal y clasificación
    Route::get('/',                    [BattleshipController::class, 'index'])->name('index');
    Route::get('/leaderboard',        [BattleshipController::class, 'leaderboard'])->name('leaderboard');

    // Crear nueva partida
    Route::get('/create',             [BattleshipController::class, 'create'])->name('create');
    Route::post('/',                  [BattleshipController::class, 'store'])->name('store');

    // Mostrar partida (setup o play)
    Route::get('/{battleship_game}',  [BattleshipController::class, 'show'])->name('show');

    // AJAX: setup, move y estado
    Route::post('/{battleship_game}/setup', [BattleshipController::class, 'setup'])->name('setup');
    Route::post('/{battleship_game}/move',  [BattleshipController::class, 'move'])->name('move');
    Route::get('/{battleship_game}/state',  [BattleshipController::class, 'state'])->name('state');
});
require __DIR__.'/auth.php';
