<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class BattleshipMove extends Model
{
    use HasFactory;

    protected $table = 'battleship_moves';

    protected $fillable = [
        'game_id',
        'shooter',  // 'player' o 'opponent'
        'x',
        'y',
        'result', // 'agua','tocado' o 'hundido'
    ];

    /**
     * La partida en la que se hizo este movimiento.
     */
    public function game(): BelongsTo
    {
        return $this->belongsTo(BattleshipGame::class, 'game_id');
    }
}

