<?php

namespace App\Events;

use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use App\Models\BattleshipGame;

class GameJoined implements ShouldBroadcastNow
{
    public function __construct(public BattleshipGame $game) {}

    public function broadcastOn()
    {
        return new PrivateChannel("battleship.pvp.{$this->game->id}");
    }

    public function broadcastWith()
    {
        return ['gameId'=>$this->game->id];
    }
}
