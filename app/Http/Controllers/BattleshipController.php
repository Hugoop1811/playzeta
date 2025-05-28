<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use App\Models\BattleshipGame;
use App\Models\BattleshipBoard;

class BattleshipController extends Controller
{
    /**
     * 1) Listado de partidas del usuario (o mensaje si no hay sesión).
     */
    public function index()
    {
        $games = Auth::check()
            ? BattleshipGame::where('user_id', Auth::id())
                             ->orderBy('created_at','desc')
                             ->get()
            : null;

        return view('games.battleship.index', compact('games'));
    }

    /**
     * 2) Formulario genérico para crear partida (IA o PVP).
     */
    public function create()
    {
        $modes = [
            'IA'  => 'Vs IA (Juego contra la máquina)',
            'PVP' => 'Multijugador Online',
        ];
        $difficulties = [
            'easy'   => 'Fácil',
            'medium' => 'Medio',
            'hard'   => 'Difícil',
        ];

        return view('games.battleship.create', compact('modes','difficulties'));
    }

    /**
     * 3) Almacenar partida y redirigir al flujo IA o PVP.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'mode'       => ['required', Rule::in(['IA','PVP'])],
            'difficulty' => ['nullable', Rule::in(['easy','medium','hard'])],
        ]);

        if ($data['mode'] === 'IA') {
            $request->validate([
                'difficulty' => ['required', Rule::in(['easy','medium','hard'])]
            ]);
        }

        // Generar token solo en PVP
        $inviteToken = $data['mode'] === 'PVP'
            ? Str::uuid()->toString()
            : null;

        // Crear partida
        $game = BattleshipGame::create([
            'user_id'      => Auth::id(),
            'opponent_id'  => null,
            'mode'         => $data['mode'],
            'difficulty'   => $data['mode']==='IA' ? $data['difficulty'] : null,
            'status'       => 'setup',
            'turn'         => 'player',
            'invite_token' => $inviteToken,
        ]);

        // Crear ambos tableros vacíos
        foreach (['player','opponent'] as $owner) {
            BattleshipBoard::create([
                'game_id' => $game->id,
                'owner'   => $owner,
                'ships'   => [],
                'hits'    => [],
            ]);
        }

        // Redirigir al flujo correspondiente
        if ($data['mode'] === 'IA') {
            return redirect()->route('battleship.ia.setup.view', $game);
        }

        return redirect()->route('battleship.pvp.lobby', $game);
    }

    /**
     * 5) Ranking / leaderboard.
     */
    public function leaderboard()
    {
        // Aquí podrías recoger puntuaciones de BattleshipGame::with('score')->…
        return view('games.battleship.leaderboard');
    }
}