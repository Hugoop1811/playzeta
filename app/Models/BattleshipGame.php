<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\{BelongsTo, HasMany, HasOne};
use Illuminate\Database\Eloquent\Factories\HasFactory;

class BattleshipGame extends Model
{
    use HasFactory;

    protected $table = 'battleship_games';

    protected $fillable = [
        'user_id',
        'opponent_id',
        'mode',
        'difficulty',
        'status',
        'turn',
        'invite_token',
    ];

    /**
     * El creador de la partida (nullable para IA sin login).
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * El rival en PVP (nullable hasta que alguien se una).
     */
    public function opponent(): BelongsTo
    {
        return $this->belongsTo(User::class, 'opponent_id');
    }

    /**
     * Los dos tableros asociados (player y opponent).
     */
    public function boards(): HasMany
    {
        return $this->hasMany(BattleshipBoard::class, 'game_id');
    }

    /**
     * Todos los movimientos realizados en esta partida.
     */
    public function moves(): HasMany
    {
        return $this->hasMany(BattleshipMove::class, 'game_id');
    }

    /**
     * La puntuación/resultados finales de la partida.
     */
    public function score(): HasOne
    {
        return $this->hasOne(BattleshipScore::class, 'game_id');
    }
}
