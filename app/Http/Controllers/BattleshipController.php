<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Str;
use App\Models\BattleshipGame;
use App\Models\BattleshipBoard;
use App\Models\BattleshipMove;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;


class BattleshipController extends Controller
{
    /** 1. Muestra todas las partidas del usuario (o públicas) */
    public function index()
    {
        if (Auth::check()) {
            // Solo las partidas del usuario autenticado
            $games = BattleshipGame::where('user_id', Auth::id())
                ->orderBy('created_at', 'desc')
                ->get();
            return view('games.battleship.index', compact('games'));
        }

        // Si no hay sesión, no le pasamos $games
        return view('games.battleship.index');
    }

    /** 2. Formulario para elegir modo/dificultad */
    public function create()
    {
        // Opciones de modo y dificultad para la vista
        $modes = [
            'IA'  => 'Vs IA (Juego contra la máquina)',
            'PVP' => 'Multijugador Online',
        ];
        $difficulties = [
            'easy'   => 'Fácil',
            'medium' => 'Medio',
            'hard'   => 'Difícil',
        ];

        // Se pasa a resources/views/games/battleship/create.blade.php
        return view('games.battleship.create', compact('modes', 'difficulties'));
    }

    /** 2.1. Crea la partida y redirige a setup o lobby */
    public function store(Request $request)
    {
        // 1) Validación básica
        $data = $request->validate([
            'mode'       => ['required', Rule::in(['IA', 'PVP'])],
            // dificultad sólo tiene sentido en IA; permitimos null pero validamos abajo
            'difficulty' => ['nullable', Rule::in(['easy', 'medium', 'hard'])],
        ]);

        // 2) Si es VS IA, la dificultad es obligatoria
        if ($data['mode'] === 'IA') {
            $request->validate([
                'difficulty' => ['required', Rule::in(['easy', 'medium', 'hard'])]
            ]);
        }

        // 3) Generar token de invitación sólo para PVP
        $inviteToken = null;
        if ($data['mode'] === 'PVP') {
            // UUID es suficientemente único; la columna admite hasta 64 chars
            $inviteToken = Str::uuid()->toString();
        }

        // 4) Crear la partida
        //    - user_id: null si guest (IA sin login), o el ID si hay sesión
        //    - opponent_id permanece null hasta que alguien se una en PVP
        $game = BattleshipGame::create([
            'user_id'      => Auth::id(),
            'opponent_id'  => null,
            'mode'         => $data['mode'],
            'difficulty'   => $data['mode'] === 'IA' ? $data['difficulty'] : null,
            'status'       => 'setup',
            'turn'         => 'player',
            'invite_token' => $inviteToken,
        ]);

        // 5) Crear los dos tableros vacíos (player y opponent)
        foreach (['player', 'opponent'] as $owner) {
            BattleshipBoard::create([
                'game_id' => $game->id,
                'owner'   => $owner,
                'ships'   => [],  // array vacío, se casteará a JSON
                'hits'    => [],  // idem
            ]);
        }

        // 6) Redirigir al formulario de colocación de barcos
        return redirect()->route('battleship.setup.view', $game);
    }

    /** 3.1. Muestra el tablero de colocación */
    public function showSetup(BattleshipGame $battleship_game)
    {
        // Si es PVP y aún no hay oponente, vamos al lobby
        if ($battleship_game->mode === 'PVP' && is_null($battleship_game->opponent_id)) {
            return redirect()->route('battleship.lobby', $battleship_game);
        }

        // Si la partida ya no está en 'setup', vamos a jugar
        if ($battleship_game->status !== 'setup') {
            return redirect()->route('battleship.play', $battleship_game);
        }

        // Cargamos el tablero “player”
        $board = $battleship_game->boards()
            ->where('owner', 'player')
            ->firstOrFail();

        // Pasamos la partida y el tablero a la vista
        return view('games.battleship.setup', [
            'battleship_game' => $battleship_game,
            'board'           => $board,
        ]);
    }

    /** 3.2. Guarda posiciones; si IA genera rival y cambia status */
    /**
     * Recibe la configuración de barcos del jugador y, si es IA,
     * crea también los del oponente y arranca la partida.
     */
    public function setup(Request $request, BattleshipGame $battleship_game)
    {
        // 1) Validar payload
        $data = $request->validate([
            'ships' => 'required|array|min:5|max:5',
            'ships.*.size'  => 'required|integer|min:2|max:5',
            'ships.*.cells' => 'required|array',
        ]);

        // 2) Guardar los barcos del jugador
        $playerBoard = $battleship_game
            ->boards()
            ->where('owner', 'player')
            ->firstOrFail();
        $playerBoard->ships = $data['ships'];
        $playerBoard->save();

        // 3) Modo IA: generar tablero rival y arrancar
        if ($battleship_game->mode === 'IA') {
            $oppBoard = $battleship_game
                ->boards()
                ->where('owner', 'opponent')
                ->firstOrFail();

            $oppBoard->ships = $this->generateRandomShips();
            $oppBoard->save();

            $battleship_game->status = 'playing';
            $battleship_game->save();

            return response()->json([
                'ok'    => true,
                'start' => true,
            ]);
        }

        // 4) Modo PVP: comprobamos si el otro ya colocó sus barcos
        $oppBoard = $battleship_game
            ->boards()
            ->where('owner', 'opponent')
            ->firstOrFail();

        $otherPlaced = ! empty($oppBoard->ships);
        if ($otherPlaced) {
            // Ambos listos: arrancamos
            $battleship_game->status = 'playing';
            $battleship_game->save();

            return response()->json([
                'ok'    => true,
                'start' => true,
            ]);
        }

        // El otro aún no ha colocado: esperamos en lobby/setup
        return response()->json([
            'ok'    => true,
            'start' => false,
        ]);
    }

    /**
     * Genera aleatoriamente la colocación de los barcos para la IA
     * sin solaparse. Devuelve array de ['size'=>int,'cells'=>[[x,y],…]].
     */
    protected function generateRandomShips(): array
    {
        $lengths = [5, 4, 3, 3, 2];
        $placed  = [];
        $grid    = array_fill(0, 10, array_fill(0, 10, false));

        foreach ($lengths as $size) {
            do {
                $ori = rand(0, 1) ? 'horizontal' : 'vertical';
                $x   = rand(0, $ori === 'horizontal' ? 10 - $size : 9);
                $y   = rand(0, $ori === 'vertical'   ? 10 - $size : 9);
                $cells = [];
                for ($i = 0; $i < $size; $i++) {
                    $xi = $ori === 'horizontal' ? $x + $i : $x;
                    $yi = $ori === 'vertical'   ? $y + $i : $y;
                    $cells[] = [$xi, $yi];
                }
                // validar solapes y adyacencia ortogonal
                $ok = true;
                foreach ($cells as [$xi, $yi]) {
                    if ($grid[$yi][$xi]) {
                        $ok = false;
                        break;
                    }
                    foreach ([[1, 0], [-1, 0], [0, 1], [0, -1]] as [$dx, $dy]) {
                        $nx = $xi + $dx;
                        $ny = $yi + $dy;
                        if ($nx >= 0 && $nx < 10 && $ny >= 0 && $ny < 10 && $grid[$ny][$nx]) {
                            $ok = false;
                            break 2;
                        }
                    }
                }
            } while (! $ok);
            foreach ($cells as [$xi, $yi]) {
                $grid[$yi][$xi] = true;
            }
            $placed[] = ['size' => $size, 'cells' => $cells];
        }

        return $placed;
    }

    /** 4.1. Muestra el tablero de juego */
    public function showPlay(BattleshipGame $battleship_game)
    {
        // Si aún en setup, redirigimos allí
        if ($battleship_game->status === 'setup') {
            return redirect()->route('battleship.setup.view', $battleship_game);
        }

        // Si PVP y sin oponente, al lobby
        if ($battleship_game->mode === 'PVP' && is_null($battleship_game->opponent_id)) {
            return redirect()->route('battleship.lobby', $battleship_game);
        }

        // Cargamos ambos tableros
        $playerBoard = $battleship_game->boards()
            ->where('owner', 'player')
            ->firstOrFail();
        $oppBoard    = $battleship_game->boards()
            ->where('owner', 'opponent')
            ->firstOrFail();

        return view('games.battleship.play', [
            'battleship_game' => $battleship_game,
            'playerBoard'     => $playerBoard,
            'oppBoard'        => $oppBoard,
        ]);
    }

    /** 4.2. Procesa un disparo; retorna JSON con resultados y estado */
    public function move(Request $request, BattleshipGame $battleship_game)
    {
        $data = $request->validate([
            'x' => 'required|integer|min:0|max:9',
            'y' => 'required|integer|min:0|max:9',
        ]);

        if ($battleship_game->status !== 'playing') {
            return response()->json(['message' => 'La partida no está en curso.'], 422);
        }
        if ($battleship_game->turn !== 'player') {
            return response()->json(['message' => 'No es tu turno.'], 422);
        }

        $oppBoard    = $battleship_game->boards()->where('owner', 'opponent')->firstOrFail();
        $playerBoard = $battleship_game->boards()->where('owner', 'player')->firstOrFail();

        // 1) Disparo del jugador
        $shotP = $this->processShot($oppBoard, $data['x'], $data['y']);
        BattleshipMove::create([
            'game_id' => $battleship_game->id,
            'shooter' => 'player',
            'x'       => $data['x'],
            'y'       => $data['y'],
            'result'  => $shotP['result'],
        ]);

        // Si el jugador hunde todo — fin inmediato
        if ($shotP['gameOver']) {
            $battleship_game->status = 'finished';
            $battleship_game->save();
            return response()->json([
                'resultPlayer' => $shotP['result'],
                'sunkCells'   => $shotP['cells'],
                'coordsAI'    => null,
                'resultAI'    => null,
                'gameOver'    => true,
                'winner'      => 'player',
                'turn'        => 'player',
            ]);
        }

        // 2) Disparo de la IA
        [$ax, $ay, $resAI, $sunkI, $overI, $cellsI] = $this->aiShot($playerBoard);
        BattleshipMove::create([
            'game_id' => $battleship_game->id,
            'shooter' => 'opponent',
            'x'       => $ax,
            'y'       => $ay,
            'result'  => $resAI,
        ]);

        // Si la IA hunde todo — fin
        if ($overI) {
            $battleship_game->status = 'finished';
            $battleship_game->save();
            return response()->json([
                'resultPlayer' => $shotP['result'],
                'sunkCells'   => $shotP['cells'],
                'coordsAI'    => [$ax, $ay],
                'resultAI'    => $resAI,
                'gameOver'    => true,
                'winner'      => 'opponent',
                'turn'        => 'player',
            ]);
        }

        // 3) Continuar partida
        $battleship_game->turn = 'player';
        $battleship_game->save();

        return response()->json([
            'resultPlayer' => $shotP['result'],
            'sunkCells'   => $shotP['cells'],
            'coordsAI'    => [$ax, $ay],
            'resultAI'    => $resAI,
            'gameOver'    => false,
            'winner'      => null,
            'turn'        => 'player',
        ]);
    }

    /**
     * Anota el disparo, determina resultado y si terminó el juego.
     * Devuelve ['result'=>string,'cells'=>array(listado de celdas hundidas),'gameOver'=>bool].
     */
    protected function processShot(BattleshipBoard $board, int $x, int $y): array
    {
        $hits  = $board->hits  ?? [];
        $ships = $board->ships ?? [];

        // Evitar repetir disparo
        if (collect($hits)->contains(fn($h) => $h[0] === $x && $h[1] === $y)) {
            return ['result' => 'agua', 'cells' => [], 'gameOver' => false];
        }

        // Anotar disparo
        $hits[] = [$x, $y];
        $board->hits = $hits;
        $board->save();

        // ¿Ha tocado barco?
        $hitShip = null;
        foreach ($ships as $s) {
            if (collect($s['cells'])->contains(fn($c) => $c[0] === $x && $c[1] === $y)) {
                $hitShip = $s['cells'];
                break;
            }
        }
        if (! $hitShip) {
            return ['result' => 'agua', 'cells' => [], 'gameOver' => false];
        }

        // ¿Hundido el barco?
        $allHit = collect($hitShip)->every(fn($c) => in_array($c, $hits));
        $result = $allHit ? 'hundido' : 'tocado';

        // ¿Todos los barcos hundidos? 
        $allCells = collect($ships)
            ->pluck('cells')      // [[x,y],…],[[x,y],…],…
            ->flatten(1)          // [ [x,y], [x,y], [x,y], … ]
            ->all();
        $gameOver = collect($allCells)->every(fn($c) => in_array($c, $hits));

        return [
            'result'   => $result,
            'cells'    => $allHit ? $hitShip : [],
            'gameOver' => $gameOver,
        ];
    }

    /**
     * La IA dispara a una casilla libre, usa processShot y devuelve
     * [x,y,result,sunk,gameOver,cells].
     */
    protected function aiShot(BattleshipBoard $board): array
    {
        $difficulty = strtolower($board->game->difficulty ?? 'easy');
        $hits       = $board->hits  ?? [];
        $ships      = $board->ships ?? [];

        // 1) Hunt & Target avanzado para medium/hard
        if (in_array($difficulty, ['medium', 'hard'], true)) {
            foreach ($ships as $ship) {
                // 1.1) Extraer sólo las celdas tocadas
                $hitCells = collect($ship['cells'])
                    ->filter(fn($c) => in_array($c, $hits, true))
                    ->values()
                    ->all();

                $hitCount = count($hitCells);
                $total    = count($ship['cells']);

                // Sólo si hay al menos 1 hit y no está hundido
                if ($hitCount > 0 && $hitCount < $total) {
                    $candidates = [];

                    // 1.2) Si hay >=2 hits, detectamos orientación
                    if ($hitCount >= 2) {
                        $xs = array_column($hitCells, 0);
                        $ys = array_column($hitCells, 1);

                        // Vertical: mismos X
                        if (count(array_unique($xs)) === 1) {
                            $x0    = $xs[0];
                            $minY  = min($ys);
                            $maxY  = max($ys);
                            // Propongo primero extremo superior, luego inferior
                            $candidates[] = [$x0, $minY - 1];
                            $candidates[] = [$x0, $maxY + 1];
                        }
                        // Horizontal: mismos Y
                        elseif (count(array_unique($ys)) === 1) {
                            $y0    = $ys[0];
                            $minX  = min($xs);
                            $maxX  = max($xs);
                            $candidates[] = [$minX - 1, $y0];
                            $candidates[] = [$maxX + 1, $y0];
                        }

                        // Filtrar fuera de rango y ya disparadas
                        $candidates = array_filter(
                            $candidates,
                            fn($c) =>
                            $c[0] >= 0 && $c[0] < 10 &&
                                $c[1] >= 0 && $c[1] < 10 &&
                                ! in_array($c, $hits, true)
                        );
                    }

                    // 1.3) Si no obtuvimos candidato en línea, fallback ortogonal
                    if (empty($candidates)) {
                        foreach ($hitCells as [$cx, $cy]) {
                            foreach ([[1, 0], [-1, 0], [0, 1], [0, -1]] as [$dx, $dy]) {
                                $nx = $cx + $dx;
                                $ny = $cy + $dy;
                                if (
                                    $nx >= 0 && $nx < 10 &&
                                    $ny >= 0 && $ny < 10 &&
                                    ! in_array([$nx, $ny], $hits, true)
                                ) {
                                    $candidates[] = [$nx, $ny];
                                }
                            }
                        }
                    }

                    // 1.4) Eliminar duplicados
                    $candidates = collect($candidates)->unique()->values()->all();

                    if (!empty($candidates)) {
                        [$x, $y] = $candidates[array_rand($candidates)];
                        return $this->finalizeAiShot($board, $x, $y);
                    }
                    // Si aún sin candidatos, probamos siguiente barco
                }
            }
        }

        // 2) Disparo aleatorio (y paridad en hard)
        $pool = [];
        for ($i = 0; $i < 10; $i++) {
            for ($j = 0; $j < 10; $j++) {
                if (! in_array([$i, $j], $hits, true)) {
                    if ($difficulty === 'hard') {
                        if ((($i + $j) % 2) === 0) $pool[] = [$i, $j];
                    } else {
                        $pool[] = [$i, $j];
                    }
                }
            }
        }
        if (empty($pool)) {
            for ($i = 0; $i < 10; $i++) {
                for ($j = 0; $j < 10; $j++) {
                    if (! in_array([$i, $j], $hits, true)) {
                        $pool[] = [$i, $j];
                    }
                }
            }
        }
        [$x, $y] = $pool[array_rand($pool)];
        return $this->finalizeAiShot($board, $x, $y);
    }

    protected function finalizeAiShot(BattleshipBoard $board, int $x, int $y): array
    {
        $shot = $this->processShot($board, $x, $y);
        return [
            $x,
            $y,
            $shot['result'],
            ($shot['result'] === 'hundido'),
            $shot['gameOver'],
            $shot['cells'],
        ];
    }
    // ... resto de la clase ...



    /** (Opcional) Para polling si no usamos WebSockets */
    public function state(BattleshipGame $battleship_game)
    {
        // TODO: devolver JSON con hits/ships/turn/status
    }

    /** (Opcional) Ranking final */
    public function leaderboard()
    {
        // TODO: view('games.battleship.leaderboard', compact('scores'));
    }

    // … aquí luego añadiremos métodos auxiliares como processShot(), aiShot(), checkGameOver(), generateRandomShips(), etc. …
}
