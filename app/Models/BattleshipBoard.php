<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class BattleshipBoard extends Model
{
    use HasFactory;

    protected $table = 'battleship_boards';

    protected $fillable = [
        'game_id',
        'owner',
        'ships',
        'hits',
    ];

    /** 
     * Eloquent serializará automáticamente estos campos JSON a array.
     */
    protected $casts = [
        'ships' => 'array',
        'hits'  => 'array',
    ];

    /**
     * La partida a la que pertenece este tablero.
     */
    public function game(): BelongsTo
    {
        return $this->belongsTo(BattleshipGame::class, 'game_id');
    }
}
