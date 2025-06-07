<?php

namespace App\Events;

use App\Models\BattleshipGame;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Queue\SerializesModels;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;

class GameJoined implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $gameId;
    public $opponentId;

    public function __construct(BattleshipGame $game)
    {
        $this->gameId = $game->id;
        $this->opponentId = $game->opponent_id;
    }

    public function broadcastOn()
    {
        return new PrivateChannel('battleship.pvp.' . $this->gameId);
    }

    public function broadcastAs()
    {
        return 'GameJoined';
    }
}
