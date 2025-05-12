<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\WordleController;
use App\Http\Controllers\BattleshipController;
use App\Models\BattleshipGame;
use App\Http\Controllers\SpeedClickController;

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

// Speed Click
Route::get('/speedclick', [SpeedClickController::class, 'index'])->name('speedclick.index');
Route::get('/speedclick/challenge', [SpeedClickController::class, 'challenge'])->name('speedclick.challenge');



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

// Battleship (Clásico vs IA y PVP)
Route::prefix('battleship')->group(function () {
    // 1. Listado de partidas
    Route::get('/', [BattleshipController::class, 'index'])
         ->name('battleship.index');

    // 2. Crear nueva partida
    Route::get('create', [BattleshipController::class, 'create'])
         ->name('battleship.create');
    Route::post('/', [BattleshipController::class, 'store'])
         ->name('battleship.store');

    // 3. Clasificación
    Route::get('leaderboard', [BattleshipController::class, 'leaderboard'])
         ->name('battleship.leaderboard');

    // 4. Lobby PVP: compartir enlace y esperar rival
    Route::get('{battleship_game}/lobby', [BattleshipController::class, 'lobby'])
         ->whereNumber('battleship_game')
         ->name('battleship.lobby');

    // 5. Join PVP: el segundo jugador se une
    Route::get('{battleship_game}/join', [BattleshipController::class, 'join'])
         ->whereNumber('battleship_game')
         ->name('battleship.join');

    // 6. GET formulario de setup (colocar barcos)
    Route::get('{battleship_game}/setup', [BattleshipController::class, 'showSetup'])
         ->whereNumber('battleship_game')
         ->name('battleship.setup.view');

    // 7. POST positions de setup
    Route::post('{battleship_game}/setup', [BattleshipController::class, 'setup'])
         ->whereNumber('battleship_game')
         ->name('battleship.setup');

    // 8. GET pantalla de juego (play)
    Route::get('{battleship_game}/play', [BattleshipController::class, 'showPlay'])
         ->whereNumber('battleship_game')
         ->name('battleship.play');

    // 9. POST disparo
    Route::post('{battleship_game}/move', [BattleshipController::class, 'move'])
         ->whereNumber('battleship_game')
         ->name('battleship.move');

    // 10. Estado completo (polling PVP)
    Route::get('{battleship_game}/state', [BattleshipController::class, 'state'])
         ->whereNumber('battleship_game')
         ->name('battleship.state');
});

require __DIR__ . '/auth.php';
