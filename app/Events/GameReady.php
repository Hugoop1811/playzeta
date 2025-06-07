<?php

namespace App\Events;

use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Broadcasting\PrivateChannel;

class GameReady implements ShouldBroadcast
{
    use InteractsWithSockets, SerializesModels;

    public int $gameId;

    public function __construct(int $gameId)
    {
        $this->gameId = $gameId;
    }

    public function broadcastOn()
    {
        return new PrivateChannel('battleship.pvp.' . $this->gameId);
    }

    public function broadcastAs(): string
    {
        return 'GameReady';
    }
}
