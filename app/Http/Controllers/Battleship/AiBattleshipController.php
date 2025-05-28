<?php
// app/Http/Controllers/Battleship/AiBattleshipController.php

namespace App\Http\Controllers\Battleship;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use App\Models\BattleshipGame;
use App\Models\BattleshipBoard;
use App\Models\BattleshipMove;
use App\Services\Battleship\GameEngine;

class AiBattleshipController extends Controller
{
    protected GameEngine $engine;

    public function __construct(GameEngine $engine)
    {
        $this->engine = $engine;
    }
    
    public function showSetup(BattleshipGame $battleship_game)
    {
        if ($battleship_game->status !== 'setup') {
            return redirect()->route('battleship.ia.play', $battleship_game);
        }
        $board = $battleship_game->boards()
            ->where('owner','player')
            ->firstOrFail();

        return view('games.battleship.ia.setup', compact('battleship_game','board'));
    }

    public function setup(Request $request, BattleshipGame $battleship_game)
    {
        $data = $request->validate([
            'ships'          => 'required|array|min:5|max:5',
            'ships.*.size'   => 'required|integer|min:2|max:5',
            'ships.*.cells'  => 'required|array',
        ]);

        // Placing player ships
        $playerBoard = $battleship_game->boards()
            ->where('owner','player')
            ->firstOrFail();
        $this->engine->placeShips($playerBoard, $data['ships']);

        // Generating & placing AI ships
        $oppBoard = $battleship_game->boards()
            ->where('owner','opponent')
            ->firstOrFail();
        $randomShips = $this->engine->generateRandomShips();
        $this->engine->placeShips($oppBoard, $randomShips);

        // Start game
        $battleship_game->status = 'playing';
        $battleship_game->save();

        return response()->json(['ok'=>true,'start'=>true]);
    }

    public function showPlay(BattleshipGame $battleship_game)
    {
        if ($battleship_game->status === 'setup') {
            return redirect()->route('battleship.ia.setup.view', $battleship_game);
        }

        $playerBoard = $battleship_game->boards()
            ->where('owner','player')
            ->firstOrFail();
        $oppBoard = $battleship_game->boards()
            ->where('owner','opponent')
            ->firstOrFail();

        return view('games.battleship.ia.play', compact('battleship_game','playerBoard','oppBoard'));
    }

    public function move(Request $request, BattleshipGame $battleship_game)
    {
        $data = $request->validate([
            'x' => 'required|integer|min:0|max:9',
            'y' => 'required|integer|min:0|max:9',
        ]);

        if ($battleship_game->status !== 'playing') {
            return response()->json(['message'=>'La partida no está en curso.'], 422);
        }
        if ($battleship_game->turn !== 'player') {
            return response()->json(['message'=>'No es tu turno.'], 422);
        }

        $oppBoard    = $battleship_game->boards()->where('owner','opponent')->firstOrFail();
        $playerBoard = $battleship_game->boards()->where('owner','player')->firstOrFail();

        // Jugador dispara
        $shotP = $this->engine->processShot($oppBoard, $data['x'], $data['y']);
        BattleshipMove::create([
            'game_id' => $battleship_game->id,
            'shooter' => 'player',
            'x'       => $data['x'],
            'y'       => $data['y'],
            'result'  => $shotP['result'],
        ]);

        if ($shotP['gameOver']) {
            $battleship_game->status = 'finished';
            $battleship_game->save();
            return response()->json([
                'resultPlayer'=> $shotP['result'],
                'sunkCells'   => $shotP['cells'],
                'coordsAI'    => null,
                'resultAI'    => null,
                'gameOver'    => true,
                'winner'      => 'player',
                'turn'        => 'player',
            ]);
        }

        // IA dispara
        [$ax,$ay,$resultAI,$cellsAI,$overAI] = $this->engine->aiShot($playerBoard);
        BattleshipMove::create([
            'game_id' => $battleship_game->id,
            'shooter' => 'opponent',
            'x'       => $ax,
            'y'       => $ay,
            'result'  => $resultAI,
        ]);

        if ($overAI) {
            $battleship_game->status = 'finished';
            $battleship_game->save();
            return response()->json([
                'resultPlayer'=> $shotP['result'],
                'sunkCells'   => $shotP['cells'],
                'coordsAI'    => [$ax,$ay],
                'resultAI'    => $resultAI,
                'gameOver'    => true,
                'winner'      => 'opponent',
                'turn'        => 'player',
            ]);
        }

        $battleship_game->turn = 'player';
        $battleship_game->save();

        return response()->json([
            'resultPlayer'=> $shotP['result'],
            'sunkCells'   => $shotP['cells'],
            'coordsAI'    => [$ax,$ay],
            'resultAI'    => $resultAI,
            'gameOver'    => false,
            'winner'      => null,
            'turn'        => 'player',
        ]);
    }
}