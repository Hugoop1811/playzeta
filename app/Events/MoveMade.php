<?php

namespace App\Events;

use App\Models\BattleshipGame;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Queue\SerializesModels;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;

class MoveMade implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $gameId;
    public $shooter;
    public $coordinates;
    public $result;
    public $sunkCells;
    public $gameOver;
    public $winner;

    public function __construct(
        int $gameId,
        string $shooter,
        array $coordinates,
        string $result,
        array $sunkCells,
        bool $gameOver,
        ?string $winner
    ) {
        $this->gameId = $gameId;
        $this->shooter = $shooter;
        $this->coordinates = $coordinates;
        $this->result = $result;
        $this->sunkCells = $sunkCells;
        $this->gameOver = $gameOver;
        $this->winner = $winner;
    }

    public function broadcastOn()
    {
        return new PrivateChannel('battleship.pvp.' . $this->gameId);
    }

    public function broadcastAs()
    {
        return 'MoveMade';
    }

    public function broadcastWith()
    {
        $payload = [
            'coordinates' => $this->coordinates,
            'result'      => $this->result,
            'shooter'     => $this->shooter,
            'cells'       => $this->sunkCells,
            'gameOver'    => $this->gameOver,
            'winner'      => $this->winner,
        ];

        if ($this->gameOver) {
            $game = BattleshipGame::with('boards')->find($this->gameId);
            $opponentBoard = $game->boards
                ->firstWhere('owner', $this->shooter === 'player' ? 'player' : 'opponent');

            $payload['opponentShips'] = $opponentBoard?->ships ?? [];
        }

        return $payload;
    }
}
