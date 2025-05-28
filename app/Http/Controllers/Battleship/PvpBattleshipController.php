<?php
// app/Http/Controllers/Battleship/PvpBattleshipController.php

namespace App\Http\Controllers\Battleship;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use App\Models\BattleshipGame;
use App\Models\BattleshipBoard;
use App\Models\BattleshipMove;
use App\Events\GameJoined;
use App\Events\ShipsPlaced;
use App\Events\MoveMade;
use App\Events\GameOver;
use App\Services\Battleship\GameEngine;

class PvpBattleshipController extends Controller
{
    protected GameEngine $engine;

    public function __construct(GameEngine $engine)
    {
        $this->engine = $engine;
    }

    /** 3) Lobby: muestra enlace de invitación */
    public function lobby(BattleshipGame $battleship_game)
    {
        return view('games.battleship.pvp.lobby', compact('battleship_game'));
    }

    /** 4) Unirse via token */
    public function join(BattleshipGame $battleship_game)
    {
        // 1. Impide unirse si ya hay rival
        if ($battleship_game->opponent_id) {
            abort(403, 'Esta partida ya está completa.');
        }

        // 2. Guarda al segundo jugador
        $battleship_game->opponent_id = Auth::id();
        $battleship_game->save();

        // 3. Dispara el evento para notificar al primero
        event(new GameJoined($battleship_game));

        // 4. REDIRIGE al SETUP en lugar del LOBBY
        return redirect()
            ->route('battleship.pvp.setup.view', $battleship_game);
    }

    /** 5) Mostrar setup (esperar a que ambos estén) */
    public function showSetup(BattleshipGame $battleship_game)
    {
        if (is_null($battleship_game->opponent_id)) {
            return redirect()->route('battleship.pvp.lobby', $battleship_game);
        }
        if ($battleship_game->status !== 'setup') {
            return redirect()->route('battleship.pvp.play', $battleship_game);
        }

        $owner = $this->isCreator($battleship_game) ? 'player' : 'opponent';
        $board = $battleship_game->boards()
            ->where('owner', $owner)
            ->firstOrFail();

        return view('games.battleship.pvp.setup', compact('battleship_game', 'board'));
    }

    /** 6) Guardar posiciones y, si ambos listos, arrancar */
    public function setup(Request $request, BattleshipGame $battleship_game)
    {
        $data = $request->validate([
            'ships'         => 'required|array|min:5|max:5',
            'ships.*.size'  => 'required|integer|min:2|max:5',
            'ships.*.cells' => 'required|array',
        ]);

        $owner = $this->isCreator($battleship_game) ? 'player' : 'opponent';
        $board = $battleship_game->boards()
            ->where('owner', $owner)
            ->firstOrFail();

        $this->engine->placeShips($board, $data['ships']);

        event(new ShipsPlaced($battleship_game));

        // Si el rival ya colocó:
        $oppBoard = $battleship_game->boards()->where('owner', 'opponent')->first();
        $otherReady = ! empty($oppBoard->ships);

        if ($otherReady) {
            $battleship_game->status = 'playing';
            $battleship_game->save();
            return response()->json(['ok' => true, 'start' => true]);
        }

        return response()->json(['ok' => true, 'start' => false]);
    }

    /** 7) Mostrar partida */
    public function showPlay(BattleshipGame $battleship_game)
    {
        if ($battleship_game->status === 'setup' || is_null($battleship_game->opponent_id)) {
            return redirect()->route('battleship.pvp.setup.view', $battleship_game);
        }

        $playerBoard = $battleship_game->boards()
            ->where('owner', 'player')
            ->firstOrFail();
        $oppBoard    = $battleship_game->boards()
            ->where('owner', 'opponent')
            ->firstOrFail();

        return view('games.battleship.pvp.play', compact('battleship_game', 'playerBoard', 'oppBoard'));
    }

    /** 8) Disparar contra el rival */
    public function move(Request $request, BattleshipGame $battleship_game)
    {
        $data = $request->validate([
            'x' => 'required|integer|min:0|max:9',
            'y' => 'required|integer|min:0|max:9',
        ]);

        if ($battleship_game->status !== 'playing') {
            return response()->json(['message' => 'La partida no está en curso.'], 422);
        }

        $shooter = $this->isCreator($battleship_game) ? 'player' : 'opponent';
        if ($battleship_game->turn !== $shooter) {
            return response()->json(['message' => 'No es tu turno.'], 422);
        }

        $target = $shooter === 'player' ? 'opponent' : 'player';
        $board  = $battleship_game->boards()
            ->where('owner', $target)
            ->firstOrFail();

        $shot = $this->engine->processShot($board, $data['x'], $data['y']);

        BattleshipMove::create([
            'game_id' => $battleship_game->id,
            'shooter' => $shooter,
            'x'       => $data['x'],
            'y'       => $data['y'],
            'result'  => $shot['result'],
        ]);

        event(new MoveMade(
            $battleship_game->id,
            $shooter,
            [$data['x'], $data['y']],
            $shot['result'],
            $shot['cells'],
            $shot['gameOver'],
            $shot['gameOver'] ? $shooter : null
        ));

        if ($shot['gameOver']) {
            $battleship_game->status = 'finished';
            $battleship_game->save();
            event(new GameOver($battleship_game->id, $shooter));
            return response()->json(['gameOver' => true, 'winner' => $shooter]);
        }

        $battleship_game->turn = $target;
        $battleship_game->save();

        return response()->json([
            'gameOver' => false,
            'turn'    => $target,
        ]);
    }

    private function isCreator(BattleshipGame $game): bool
    {
        return Auth::id() === $game->user_id;
    }
}
