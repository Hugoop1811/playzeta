<?php

namespace App\Events;

use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;

class GameOver implements ShouldBroadcast
{
    use Dispatchable;

    public int    $gameId;
    public string $winner;   // 'player' o 'opponent'

    public function __construct(int $gameId, string $winner)
    {
        $this->gameId = $gameId;
        $this->winner = $winner;
    }

    public function broadcastOn(): PrivateChannel
    {
        return new PrivateChannel("battleship.pvp.{$this->gameId}");
    }

    public function broadcastWith(): array
    {
        return [
            'gameId' => $this->gameId,
            'winner' => $this->winner,
        ];
    }
}