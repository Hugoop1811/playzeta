<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\WordleController;
use App\Http\Controllers\BattleshipController;
use App\Models\BattleshipGame;
use App\Http\Controllers\SpeedClickController;
use App\Http\Controllers\WordleTimeAttackController;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use App\Http\Controllers\WordleTimeScoreController;
use App\Http\Controllers\VolumeController;

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
     return view('games.wordle.wordle_advanced');
})->name('wordle.advanced');

Route::get('/wordle/nn-ready', function () {
     return view('games.wordle.wordle_nn_ready');
})->name('wordle.nn_ready');

// Wordle contrarreloj
Route::get('/wordle/contrarreloj', [WordleController::class, 'timeAttack'])->name('wordle.time');
Route::post('/wordle/contrarreloj/check', [WordleController::class, 'checkTimeMode'])->name('wordle.time.check');
Route::post('/wordle/contrarreloj/guardar', [WordleController::class, 'saveTimeScore'])->name('wordle.time.save');
Route::get('/wordle/contrarreloj/historial', [WordleTimeScoreController::class, 'historial'])->name('wordle.time.historial');
Route::get('/wordle/contrarreloj/leaderboard', [WordleTimeScoreController::class, 'leaderboard'])
     ->name('wordle.time.leaderboard');

Route::get('/api/wordle/random', [WordleController::class, 'getRandomWord']);
Route::post('/api/wordle/time-attack-score', [WordleController::class, 'saveTimeAttackScore'])->middleware('auth');
Route::post('/api/wordle/time-attack-score', [WordleTimeScoreController::class, 'store'])->middleware('auth');



// Speed Click
Route::get('/speedclick', [SpeedClickController::class, 'index'])->name('speedclick.index');
Route::get('/speedclick/challenge', [SpeedClickController::class, 'challenge'])->name('speedclick.challenge');



// Dashboard (usuarios logueados)
Route::get('/dashboard', function () {
   return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');


Route::get('/email/verify', function () {
    return view('auth.verify-email');
})->middleware('auth')->name('verification.notice');

Route::get('/email/verify/{id}/{hash}', function (EmailVerificationRequest $request) {
    $request->fulfill();

    return redirect('/'); // <-- Aquí decides a dónde redirigir después del click del email
})->middleware(['auth', 'signed'])->name('verification.verify');

// Perfil de usuario
Route::middleware('auth')->group(function () {
     Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
     Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
     Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// Battleship (Clásico vs IA y PVP)
Route::prefix('battleship')->name('battleship.')->group(function () {
     // 1. Listado de partidas (opcional)
     Route::get('/', [BattleshipController::class, 'index'])
          ->name('index');

     // 2. Crear nueva partida
     Route::get('create', [BattleshipController::class, 'create'])
          ->name('create');
     Route::post('/', [BattleshipController::class, 'store'])
          ->name('store');

     // 3. Setup de barcos
     Route::get('{battleship_game}/setup', [BattleshipController::class, 'showSetup'])
          ->whereNumber('battleship_game')
          ->name('setup.view');
     Route::post('{battleship_game}/setup', [BattleshipController::class, 'setup'])
          ->whereNumber('battleship_game')
          ->name('setup');

     // 4. Juego activo
     Route::get('{battleship_game}/play', [BattleshipController::class, 'showPlay'])
          ->whereNumber('battleship_game')
          ->name('play');
     Route::post('{battleship_game}/move', [BattleshipController::class, 'move'])
          ->whereNumber('battleship_game')
          ->name('move');
     Route::get('{battleship_game}/state', [BattleshipController::class, 'state'])
          ->whereNumber('battleship_game')
          ->name('state');

     // 5. Leaderboard (opcional)
     Route::get('leaderboard', [BattleshipController::class, 'leaderboard'])
          ->name('leaderboard'); // …

     // Debe ir dentro del grupo de rutas que requieren web/session:
     Route::post('volume', [VolumeController::class, 'update'])
          ->name('volume');
});

require __DIR__ . '/auth.php';
