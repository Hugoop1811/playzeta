<?php

namespace App\Events;

use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;

class MoveMade implements ShouldBroadcast
{
    use Dispatchable;

    public int    $gameId;
    public string $shooter;    // 'player' o 'opponent'
    public array  $coords;     // [x, y]
    public string $result;     // 'agua'|'tocado'|'hundido'
    public array  $sunkCells;  // [[x,y],…]
    public bool   $gameOver;
    public ?string $winner;    // 'player'|'opponent'|null

    public function __construct(
        int    $gameId,
        string $shooter,
        array  $coords,
        string $result,
        array  $sunkCells,
        bool   $gameOver,
        ?string $winner
    ) {
        $this->gameId    = $gameId;
        $this->shooter   = $shooter;
        $this->coords    = $coords;
        $this->result    = $result;
        $this->sunkCells = $sunkCells;
        $this->gameOver  = $gameOver;
        $this->winner    = $winner;
    }

    public function broadcastOn(): PrivateChannel
    {
        return new PrivateChannel("battleship.pvp.{$this->gameId}");
    }

    public function broadcastWith(): array
    {
        return [
            'shooter'   => $this->shooter,
            'coords'    => $this->coords,
            'result'    => $this->result,
            'sunkCells' => $this->sunkCells,
            'gameOver'  => $this->gameOver,
            'winner'    => $this->winner,
        ];
    }
}