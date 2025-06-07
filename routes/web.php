<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\WordleController;
use App\Http\Controllers\BattleshipController;
use App\Models\BattleshipGame;
use App\Http\Controllers\SpeedClickController;
use App\Http\Controllers\SpeedclickScoreController;
use App\Http\Controllers\SpeedclickChallengeScoreController;
use App\Http\Controllers\WordleTimeAttackController;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use App\Http\Controllers\WordleTimeScoreController;
use App\Http\Controllers\VolumeController;
use App\Http\Controllers\Battleship\AiBattleshipController;
use App\Http\Controllers\Battleship\PvpBattleshipController;
use Illuminate\Support\Facades\Broadcast;

/*
|--------------------------------------------------------------------------
| Web Routes - PlayZeta
|--------------------------------------------------------------------------
*/
Route::view('/pusher-receiver', 'pusher.receiver');
Route::view('/pusher-sender', 'pusher.sender');
use Illuminate\Http\Request;
use App\Events\TestPusherEvent;

Route::post('/pusher-send-message', function (Request $request) {
    $message = $request->input('message');
    broadcast(new TestPusherEvent(['message' => $message]));
    return back()->with('status', 'Mensaje enviado');
});


Broadcast::routes();
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
Route::post('/speedclick/score', [SpeedclickScoreController::class, 'store']);
Route::get('/speedclick/leaderboard', [SpeedclickScoreController::class, 'leaderboard']);
Route::get('/speedclick/challenge', [SpeedClickController::class, 'challenge'])->name('speedclick.challenge');
Route::post('/speedclick/challenge/score', [SpeedclickChallengeScoreController::class, 'store']);
Route::get('/speedclick/challenge/leaderboard', [SpeedclickChallengeScoreController::class, 'leaderboard']);




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
     // 1) INDEX
     Route::get('/', [BattleshipController::class, 'index'])->name('index');

     // 2) CREATE / STORE (genérico)
     Route::get('create', [BattleshipController::class, 'create'])->name('create');
     Route::post('/',      [BattleshipController::class, 'store'])->name('store');

     // ——— IA ———
     Route::get('ia/{battleship_game}/setup', [AiBattleshipController::class, 'showSetup'])
          ->name('ia.setup.view');
     Route::post('ia/{battleship_game}/setup', [AiBattleshipController::class, 'setup'])
          ->name('ia.setup');
     Route::get('ia/{battleship_game}/play',  [AiBattleshipController::class, 'showPlay'])
          ->name('ia.play');
     Route::post('ia/{battleship_game}/move', [AiBattleshipController::class, 'move'])
          ->name('ia.move');

     // ——— PVP ———
     Route::prefix('pvp')->middleware('auth')->name('pvp.')->group(function () {
          Route::get('{battleship_game}/lobby', [PvpBattleshipController::class, 'lobby'])
               ->name('lobby');
          Route::get('{battleship_game}/join',  [PvpBattleshipController::class, 'join'])
               ->name('join');
          Route::get('{battleship_game}/setup',  [PvpBattleshipController::class, 'showSetup'])
               ->name('setup.view');
          Route::post('{battleship_game}/setup', [PvpBattleshipController::class, 'setup'])
               ->name('setup');
          Route::get('{battleship_game}/play',   [PvpBattleshipController::class, 'showPlay'])
               ->name('play');
          Route::post('{battleship_game}/move',  [PvpBattleshipController::class, 'move'])
               ->name('move');
     });

     // LEADERBOARD
     Route::get('leaderboard', [BattleshipController::class, 'leaderboard'])
          ->name('leaderboard');
});

Route::get('/test-pusher', function () {
     return view('test-pusher');
})->middleware('auth');

Route::post('/api/battleship/audio', function (Illuminate\Http\Request $request) {
    $vol = $request->input('volume');
    if (is_numeric($vol) && $vol >= 0 && $vol <= 1) {
        session(['battleship_bg_volume' => $vol]);
        return response()->json(['success' => true]);
    }
    return response()->json(['error' => 'Invalid volume'], 400);
});

require __DIR__ . '/auth.php';
