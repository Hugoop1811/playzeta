<?php
namespace App\Events;

use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class ShipsPlaced implements ShouldBroadcast
{
    public $gameId;
    public $playerId;

    public function __construct(int $gameId, int $playerId)
    {
        $this->gameId = $gameId;
        $this->playerId = $playerId;
    }

    public function broadcastOn()
    {
        return new PrivateChannel('battleship.' . $this->gameId);
    }

    public function broadcastWith()
    {
        return [
            'gameId' => $this->gameId,
            'playerId' => $this->playerId,
        ];
    }
}
