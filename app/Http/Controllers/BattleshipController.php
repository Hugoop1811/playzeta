<?php
namespace App\Http\Controllers;

use App\Models\BattleshipGame;
use App\Models\BattleshipScore;
use App\Models\BattleshipBoard;
use App\Models\BattleshipMove;
use App\Models\User;
use Illuminate\Http\Request;

class BattleshipController extends Controller
{
    // 1. Panel de partidas del usuario
    public function index()
    {
        // Recupera las partidas del usuario ordenadas de más recientes a más antiguas
        $games = BattleshipGame::where('user_id', auth()->id())
                    ->orderBy('created_at', 'desc')
                    ->get();

        return view('battleship.index', compact('games'));
    }

    // 2. Clasificación global de Battleship
    public function leaderboard()
    {
        // Recupera los mejores 50 resultados, con el usuario que los obtuvo
        $scores = BattleshipScore::with('user')
                     ->orderBy('score', 'desc')
                     ->limit(50)
                     ->get();

        return view('battleship.leaderboard', compact('scores'));
    }

    // 3. Formulario para elegir modo y dificultad
    public function create()
    {
        // TODO: vemos IA/PVP y niveles de dificultad
        // return view('battleship.create');
    }

    // 4. Guardar nueva partida en BD
    public function store(Request $request)
    {
        // TODO: validar, crear BattleshipGame + BattleshipBoard's en blanco
        // return redirect()->route('battleship.show', $game);
    }

    // 5. Mostrar pantalla de setup o de juego
    public function show(BattleshipGame $battleship_game)
    {
        // TODO: si status == 'setup' -> view('battleship.setup')
        // else -> view('battleship.play')
    }

    // 6. Guardar posiciones de barcos (AJAX)
    public function setup(Request $request, BattleshipGame $battleship_game)
    {
        // TODO: recibir JSON de barcos, guardar en boards, cambiar status a 'playing'
        // return response()->json(['ok' => true]);
    }

    // 7. Procesar disparo (AJAX)
    public function move(Request $request, BattleshipGame $battleship_game)
    {
        // TODO:
        //  - procesar tiro del jugador
        //  - (si modo IA) calcular tiro IA
        //  - guardar Moves y devolver JSON con resultados
    }

    // 8. Estado de la partida (AJAX polling)
    public function state(BattleshipGame $battleship_game)
    {
        // TODO: devolver JSON con arrays de hits, misses, turno, gameOver…
    }
}
