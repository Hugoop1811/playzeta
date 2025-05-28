<?php

// app/Events/ShipsPlaced.php
namespace App\Events;

use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use App\Models\BattleshipGame;
use Illuminate\Support\Facades\Auth;

class ShipsPlaced implements ShouldBroadcast
{
    public function __construct(public BattleshipGame $game){}

    public function broadcastOn()
    {
        return new PrivateChannel("battleship.pvp.{$this->game->id}");
    }

    public function broadcastWith()
    {
        return [
            'playerId' => Auth::user()->id,
        ];
    }
}

