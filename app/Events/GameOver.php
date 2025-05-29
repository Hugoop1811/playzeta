<?php

namespace App\Events;

use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Queue\SerializesModels;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;

class GameOver implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $gameId;
    public $winner;

    public function __construct(int $gameId, string $winner)
    {
        $this->gameId = $gameId;
        $this->winner = $winner;
    }

    public function broadcastOn()
    {
        return new PrivateChannel('battleship.' . $this->gameId);
    }

    public function broadcastAs()
    {
        return 'GameOver';
    }
}