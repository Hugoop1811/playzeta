<?php

use Illuminate\Support\Facades\Broadcast;
use App\Models\BattleshipGame;

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

Broadcast::channel('battleship.pvp.{gameId}', function ($user, $gameId) {
    $game = BattleshipGame::find($gameId);
    // Sólo pueden suscribirse los dos jugadores de la partida
    return $game
        && ($user->id === $game->user_id
            || $user->id === $game->opponent_id);
});
