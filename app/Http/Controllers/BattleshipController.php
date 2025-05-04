<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Models\BattleshipGame;
use App\Models\BattleshipBoard;
use App\Models\BattleshipMove;
use App\Models\BattleshipScore;

class BattleshipController extends Controller
{
    public function index()
    {
        $games = BattleshipGame::where('user_id', auth()->id())
                    ->orderBy('created_at','desc')
                    ->get();

        return view('games.battleship.index', compact('games'));
    }

    public function leaderboard()
    {
        $scores = BattleshipScore::with('user')
                     ->orderBy('score','desc')
                     ->limit(50)
                     ->get();

        return view('games.battleship.leaderboard', compact('scores'));
    }

    public function create()
    {
        return view('games.battleship.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'mode'       => ['required', Rule::in(['IA','PVP'])],
            'difficulty' => ['nullable', Rule::in(['easy','medium','hard'])],
        ]);

        $game = BattleshipGame::create([
            'user_id'     => auth()->id(),
            'opponent_id' => null,
            'mode'        => $data['mode'],
            'difficulty'  => $data['mode'] === 'IA' ? $data['difficulty'] : null,
            'status'      => 'setup',
            'turn'        => 'player',
        ]);

        foreach (['player','opponent'] as $owner) {
            BattleshipBoard::create([
                'game_id' => $game->id,
                'owner'   => $owner,
                'ships'   => [],
                'hits'    => [],
            ]);
        }

        if ($data['mode'] === 'IA') {
            return redirect()->route('battleship.setup.view', $game);
        }

        return redirect()->route('battleship.lobby', $game);
    }

    public function lobby(BattleshipGame $battleship_game)
    {
        return view('games.battleship.lobby', compact('battleship_game'));
    }

    public function join(BattleshipGame $battleship_game)
    {
        if (auth()->check()
            && $battleship_game->opponent_id === null
            && auth()->id() !== $battleship_game->user_id
        ) {
            $battleship_game->opponent_id = auth()->id();
            $battleship_game->save();
        }

        return redirect()->route('battleship.lobby', $battleship_game);
    }

    public function showSetup(BattleshipGame $battleship_game)
    {
        if ($battleship_game->mode === 'PVP'
            && $battleship_game->opponent_id === null
        ) {
            return redirect()->route('battleship.lobby', $battleship_game);
        }

        if ($battleship_game->status !== 'setup') {
            return redirect()->route('battleship.play', $battleship_game);
        }

        $board = $battleship_game->boards()
                  ->where('owner','player')
                  ->first();

        return view('games.battleship.setup', compact('battleship_game','board'));
    }

    public function setup(Request $request, BattleshipGame $battleship_game)
    {
        $data = $request->validate([
            'ships'                => 'required|array',
            'ships.*.size'         => 'required|integer|min:2|max:5',
            'ships.*.cells'        => 'required|array',
            'ships.*.cells.*.0'    => 'required|integer|min:0|max:9',
            'ships.*.cells.*.1'    => 'required|integer|min:0|max:9',
        ]);

        $meIsCreator = auth()->id() === $battleship_game->user_id;
        $owner = $meIsCreator ? 'player' : 'opponent';

        $board = $battleship_game->boards()
                 ->where('owner',$owner)
                 ->firstOrFail();
        $board->ships = $data['ships'];
        $board->save();

        if ($battleship_game->mode === 'IA') {
            $opp = $battleship_game->boards()
                   ->where('owner','opponent')
                   ->first();
            $opp->ships = $this->generateRandomShips();
            $opp->save();

            $battleship_game->status = 'playing';
            $battleship_game->save();

            return response()->json(['ok'=>true,'start'=>true]);
        }

        $otherOwner = $owner === 'player' ? 'opponent' : 'player';
        $otherBoard = $battleship_game->boards()
                       ->where('owner',$otherOwner)
                       ->first();
        $bothPlaced = ! empty($otherBoard->ships);

        if ($bothPlaced) {
            $battleship_game->status = 'playing';
            $battleship_game->save();
        }

        return response()->json(['ok'=>true,'start'=>$bothPlaced]);
    }

    public function showPlay(BattleshipGame $battleship_game)
    {
        if ($battleship_game->mode === 'PVP'
            && $battleship_game->opponent_id === null
        ) {
            return redirect()->route('battleship.lobby', $battleship_game);
        }

        if ($battleship_game->status === 'setup') {
            return redirect()->route('battleship.setup.view', $battleship_game);
        }

        return view('games.battleship.play', compact('battleship_game'));
    }

    public function move(Request $request, BattleshipGame $battleship_game)
    {
        $data = $request->validate([
            'x' => 'required|integer|min:0|max:9',
            'y' => 'required|integer|min:0|max:9',
        ]);

        $battleship_game->refresh();
        if ($battleship_game->status !== 'playing') {
            $battleship_game->status = 'playing';
            $battleship_game->save();
        }

        $playerBoard = $battleship_game->boards()->where('owner','player')->first();
        $oppBoard    = $battleship_game->boards()->where('owner','opponent')->first();

        if ($battleship_game->moves()
                 ->where('mover','player')
                 ->where('x',$data['x'])
                 ->where('y',$data['y'])
                 ->exists()
        ) {
            return response()->json(['errors'=>['move'=>['Ya has disparado ahí.']]], 422);
        }

        [$resPlayer,] = $this->processShot($oppBoard, $data['x'], $data['y']);
        $battleship_game->moves()->create([
            'mover' => 'player',
            'x'     => $data['x'],
            'y'     => $data['y'],
            'result'=> $resPlayer,
        ]);

        if ($battleship_game->mode === 'PVP') {
            $battleship_game->turn = 'opponent';
            $battleship_game->save();
        }

        $coordsAI = $resAI = null;
        if ($battleship_game->mode === 'IA') {
            [$coordsAI,$resAI] = $this->aiShot($battleship_game, $playerBoard);
        }

        [$gameOver,$winner] = $this->checkGameOver($playerBoard,$oppBoard);
        if ($gameOver) {
            $battleship_game->status = 'finished';
            $battleship_game->save();
            if ($winner === 'player') {
                BattleshipScore::create([
                    'game_id'  => $battleship_game->id,
                    'user_id'  => $battleship_game->user_id,
                    'score'    => 100,
                    'duration' => $battleship_game->updated_at
                                     ->diffInSeconds($battleship_game->created_at),
                ]);
            }
        }

        return response()->json([
            'resultPlayer'=> $resPlayer,
            'coordsAI'    => $coordsAI,
            'resultAI'    => $resAI,
            'gameOver'    => $gameOver,
            'winner'      => $winner,
            'turn'        => $battleship_game->turn,
            'status'      => $battleship_game->status,
        ]);
    }

    public function state(BattleshipGame $battleship_game)
    {
        $player = $battleship_game->boards()->where('owner','player')->first();
        $opp    = $battleship_game->boards()->where('owner','opponent')->first();

        return response()->json([
            'opponent_id'   => $battleship_game->opponent_id,
            'playerHits'    => $player->hits    ?? [],
            'playerShips'   => $player->ships   ?? [],
            'opponentHits'  => $opp->hits       ?? [],
            'opponentShips' => $opp->ships      ?? [],
            'turn'          => $battleship_game->turn,
            'status'        => $battleship_game->status,
        ]);
    }

    protected function processShot(BattleshipBoard $board, int $x, int $y): array
    {
        $ships = $board->ships;
        $hits  = $board->hits ?? [];

        foreach ($ships as $ship) {
            if (in_array([$x,$y], $ship['cells'])) {
                $hits[] = [$x,$y];
                $board->hits = $hits;
                $board->save();

                $allHit = collect($ship['cells'])
                          ->every(fn($c)=>in_array($c,$hits));

                return [$allHit ? 'hundido' : 'tocado', $allHit];
            }
        }

        $hits[] = [$x,$y];
        $board->hits = $hits;
        $board->save();

        return ['agua', false];
    }

    protected function aiShot(BattleshipGame $game, BattleshipBoard $playerBoard): array
    {
        $all = [];
        for ($i = 0; $i < 10; $i++) {
            for ($j = 0; $j < 10; $j++) {
                $all[] = [$i, $j];
            }
        }

        $shotsAI = $game->moves()
                       ->where('mover','opponent')
                       ->get()
                       ->map(fn($m)=>[$m->x,$m->y])
                       ->toArray();

        $avail = array_filter($all, fn($c)=>!in_array($c,$shotsAI));
        $choice = $avail[array_rand($avail)];
        [$x,$y] = $choice;

        [$res,] = $this->processShot($playerBoard,$x,$y);
        $game->moves()->create([
            'mover' => 'opponent',
            'x'     => $x,
            'y'     => $y,
            'result'=> $res,
        ]);

        return [[$x,$y], $res];
    }

    protected function checkGameOver(BattleshipBoard $p, BattleshipBoard $o): array
    {
        $hitsP = $p->hits ?? [];
        $hitsO = $o->hits ?? [];

        $allSunk = fn(array $ships, array $hits) =>
            collect($ships)
            ->every(fn($s)=>
                collect($s['cells'])->every(fn($c)=>in_array($c,$hits))
            );

        $oppSunk = $allSunk($o->ships,$hitsO);
        $youSunk = $allSunk($p->ships,$hitsP);

        if ($oppSunk && ! $youSunk) return [true,'player'];
        if ($youSunk) return [true,'opponent'];
        return [false,null];
    }

    private function generateRandomShips(): array
    {
        $sizes = [5,4,3,3,2];
        $ships = [];

        foreach ($sizes as $size) {
            $placed = false;
            while (! $placed) {
                $ori = rand(0,1) ? 'horizontal' : 'vertical';
                $x   = rand(0,9);
                $y   = rand(0,9);
                $cells = [];

                for ($i = 0; $i < $size; $i++) {
                    $xi = $ori === 'horizontal' ? $x + $i : $x;
                    $yi = $ori === 'vertical'   ? $y + $i : $y;
                    if ($xi > 9 || $yi > 9) {
                        $cells = [];
                        break;
                    }
                    $cells[] = [$xi, $yi];
                }

                if (count($cells) !== $size) {
                    continue;
                }

                // comprobar solapamiento manualmente
                $overlap = false;
                foreach ($ships as $existing) {
                    foreach ($existing['cells'] as $ec) {
                        foreach ($cells as $nc) {
                            if ($ec[0] === $nc[0] && $ec[1] === $nc[1]) {
                                $overlap = true;
                                break 3;
                            }
                        }
                    }
                }

                if (! $overlap) {
                    $ships[] = ['size'=>$size,'cells'=>$cells];
                    $placed  = true;
                }
            }
        }

        return $ships;
    }
}