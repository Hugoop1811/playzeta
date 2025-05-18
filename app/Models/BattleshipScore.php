<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class BattleshipScore extends Model
{
    use HasFactory;

    protected $table = 'battleship_scores';

    protected $fillable = [
        'game_id',
        'user_id',
        'score',
        'duration',
    ];

    /**
     * Partida asociada a este resultado.
     */
    public function game(): BelongsTo
    {
        return $this->belongsTo(BattleshipGame::class, 'game_id');
    }

    /**
     * Usuario que obtuvo esta puntuación.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}

