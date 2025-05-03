<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\WordleController;
use App\Http\Controllers\BattleshipController;  
use App\Models\BattleshipGame;
Route::get('/wordle', [WordleController::class, 'index'])->name('wordle.index');
Route::post('/wordle/try', [WordleController::class, 'check'])->name('wordle.check');
Route::get('/', function () {
    return view('home');
});

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