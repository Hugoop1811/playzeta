<?php
namespace App\Services\Battleship;

use App\Models\BattleshipBoard;
use App\Models\BattleshipGame;
use Illuminate\Support\Collection;

class GameEngine
{
    /**
     * Guarda el array de barcos en el tablero y persiste.
     * @param BattleshipBoard $board
     * @param array $ships     // [ ['size'=>int,'cells'=>[[x,y],…]], … ]
     */
    public function placeShips(BattleshipBoard $board, array $ships): void
    {
        $board->ships = $ships;
        $board->save();
    }

    /**
     * Genera una disposición aleatoria válida de barcos.
     * @return array
     */
    public function generateRandomShips(): array
    {
        $lengths = [5,4,3,3,2];
        $placed  = [];
        $grid    = array_fill(0,10, array_fill(0,10,false));

        foreach ($lengths as $size) {
            do {
                $ori = rand(0,1) ? 'horizontal' : 'vertical';
                $x   = rand(0, $ori==='horizontal' ? 10-$size : 9);
                $y   = rand(0, $ori==='vertical'   ? 10-$size : 9);
                $cells= [];
                for ($i=0;$i<$size;$i++) {
                    $xi = $ori==='horizontal' ? $x+$i : $x;
                    $yi = $ori==='vertical'   ? $y+$i : $y;
                    $cells[] = [$xi,$yi];
                }
                $ok = true;
                foreach ($cells as [$xi,$yi]) {
                    if ($grid[$yi][$xi]) { $ok=false; break; }
                    foreach ([[1,0],[-1,0],[0,1],[0,-1]] as [$dx,$dy]) {
                        $nx=$xi+$dx; $ny=$yi+$dy;
                        if ($nx>=0 && $nx<10 && $ny>=0 && $ny<10 && $grid[$ny][$nx]) {
                            $ok=false; break 2;
                        }
                    }
                }
            } while (! $ok);

            foreach ($cells as [$xi,$yi]) {
                $grid[$yi][$xi] = true;
            }
            $placed[] = ['size'=>$size,'cells'=>$cells];
        }

        return $placed;
    }

    /**
     * Procesa un disparo contra un tablero.
     * @param BattleshipBoard $board
     * @param int $x
     * @param int $y
     * @return array ['result'=>'agua'|'tocado'|'hundido','cells'=>array of sunk cells,'gameOver'=>bool]
     */
    public function processShot(BattleshipBoard $board, int $x, int $y): array
    {
        $hits  = $board->hits ?? [];
        $ships = $board->ships ?? [];

        // evitar repetir
        foreach ($hits as $h) {
            if ($h[0] === $x && $h[1] === $y) {
                return ['result'=>'agua','cells'=>[],'gameOver'=>false];
            }
        }

        // anotar
        $hits[] = [$x,$y];
        $board->hits = $hits;
        $board->save();

        // buscar si tocó
        $hitShip = null;
        foreach ($ships as $s) {
            foreach ($s['cells'] as $c) {
                if ($c[0] === $x && $c[1] === $y) {
                    $hitShip = $s['cells'];
                    break 2;
                }
            }
        }
        if (! $hitShip) {
            return ['result'=>'agua','cells'=>[],'gameOver'=>false];
        }

        // comprobar hundido
        $allHit = collect($hitShip)
            ->every(fn($c)=> in_array($c, $hits, true));
        $result = $allHit ? 'hundido' : 'tocado';

        // ¿todos hundidos?
        $allCells = collect($ships)
            ->pluck('cells')
            ->flatten(1)
            ->all();
        $gameOver = collect($allCells)
            ->every(fn($c)=> in_array($c, $hits, true));

        return [
            'result'   => $result,
            'cells'    => $allHit ? $hitShip : [],
            'gameOver' => $gameOver,
        ];
    }

    /**
     * IA dispara contra el tablero (para vs IA).
     * @param BattleshipBoard $board
     * @return array [x,y,result(bool sunk?),gameOver, sunkCells]
     */
    public function aiShot(BattleshipBoard $board): array
    {
        $game       = $board->game;
        $difficulty = strtolower($game->difficulty ?? 'easy');
        $hits       = $board->hits ?? [];
        $ships      = $board->ships ?? [];

        // Hunt & Target (medium/hard)
        if (in_array($difficulty, ['medium','hard'], true)) {
            foreach ($ships as $ship) {
                $hitCells = collect($ship['cells'])
                    ->filter(fn($c)=> in_array($c, $hits, true))
                    ->all();
                $hitCount = count($hitCells);
                $total    = count($ship['cells']);

                if ($hitCount>0 && $hitCount<$total) {
                    // vecinos ortogonales
                    $cands = [];
                    foreach ($hitCells as [$cx,$cy]) {
                        foreach ([[1,0],[-1,0],[0,1],[0,-1]] as [$dx,$dy]) {
                            $nx=$cx+$dx; $ny=$cy+$dy;
                            if ($nx>=0 && $nx<10 && $ny>=0 && $ny<10
                                && ! in_array([$nx,$ny], $hits, true)) {
                                $cands[] = [$nx,$ny];
                            }
                        }
                    }
                    $cands = collect($cands)->unique()->values()->all();
                    if (!empty($cands)) {
                        [$x,$y] = $cands[array_rand($cands)];
                        $shot = $this->processShot($board, $x, $y);
                        return [$x,$y,$shot['result'],$shot['cells'],$shot['gameOver']];
                    }
                }
            }
        }

        // aleatorio (paridad en hard)
        $pool = [];
        for ($i=0;$i<10;$i++){
            for ($j=0;$j<10;$j++){
                if (! in_array([$i,$j], $hits, true)) {
                    if ($difficulty==='hard' && (($i+$j)%2)!==0) continue;
                    $pool[] = [$i,$j];
                }
            }
        }
        if (empty($pool)) {
            for ($i=0;$i<10;$i++)
                for ($j=0;$j<10;$j++)
                    if (! in_array([$i,$j], $hits, true))
                        $pool[] = [$i,$j];
        }
        [$x,$y] = $pool[array_rand($pool)];
        $shot = $this->processShot($board, $x, $y);
        return [$x,$y,$shot['result'],$shot['cells'],$shot['gameOver']];
    }
}