<?php
namespace App\Http\Controllers;

use App\Models\BattleshipGame;
use App\Models\BattleshipScore;
use App\Models\BattleshipBoard;
use App\Models\BattleshipMove;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class BattleshipController extends Controller
{
    // 1. Panel de partidas del usuario
    public function index()
    {
        // Recupera las partidas del usuario ordenadas de más recientes a más antiguas
        $games = BattleshipGame::where('user_id', auth()->id())
                    ->orderBy('created_at', 'desc')
                    ->get();

        return view('games.battleship.index', compact('games'));
    }

    // 2. Clasificación global de Battleship
    public function leaderboard()
    {
        // Recupera los mejores 50 resultados, con el usuario que los obtuvo
        $scores = BattleshipScore::with('user')
                     ->orderBy('score', 'desc')
                     ->limit(50)
                     ->get();

        return view('games.battleship.leaderboard', compact('scores'));
    }

    // 3. Formulario para elegir modo y dificultad
public function create()
{
    // Devuelve la vista con el formulario
    return view('games.battleship.create');
}

// 4. Guardar nueva partida en BD
public function store(Request $request)
{
    // 1) Validamos los datos
    $data = $request->validate([
        'mode'       => ['required', Rule::in(['IA','PVP'])],
        'difficulty' => ['nullable', Rule::in(['easy','medium','hard'])],
    ]);

    // 2) Creamos la partida
    $game = BattleshipGame::create([
        'user_id'    => auth()->id(),
        'mode'       => $data['mode'],
        'difficulty' => $data['mode'] === 'IA' ? $data['difficulty'] : null,
        'status'     => 'setup',
        'turn'       => 'player',
    ]);

    // 3) Generamos dos tableros en blanco (player y opponent)
    foreach (['player','opponent'] as $owner) {
        BattleshipBoard::create([
            'game_id' => $game->id,
            'owner'   => $owner,
            'ships'   => [],  // se rellenará en setup
            'hits'    => [],
        ]);
    }

    // 4) Redirigimos a la vista de setup
    return redirect()->route('battleship.show', $game);
 }
    // 5. Mostrar pantalla de setup o de juego
    public function show(BattleshipGame $battleship_game)
{
    // Obtener el board del jugador
    $board = $battleship_game->boards()
            ->where('owner', 'player')
            ->first();

    if ($battleship_game->status === 'setup') {
        // Vista de colocación
        return view('games.battleship.setup', compact('battleship_game', 'board'));
    }

    // Si ya está en playing o finished, carga la vista de juego
    return view('games.battleship.play', compact('battleship_game', 'board'));
}

public function setup(Request $request, BattleshipGame $battleship_game)
{
    $data = $request->validate([
        'ships' => 'required|array',
        'ships.*.size'  => 'required|integer|min:2|max:5',
        'ships.*.cells' => 'required|array',
        'ships.*.cells.*.0' => 'required|integer|min:0|max:9',
        'ships.*.cells.*.1' => 'required|integer|min:0|max:9',
    ]);

    // Guardar posiciones en el board del jugador
    $board = $battleship_game->boards()
             ->where('owner', 'player')
             ->firstOrFail();

    $board->ships = $data['ships'];
    $board->save();

    // Cambiar estado de la partida a 'playing'
    $battleship_game->status = 'playing';
    $battleship_game->save();

    return response()->json(['ok' => true]);
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
