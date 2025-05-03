<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BattleshipMove extends Model
{
    protected $table = 'battleship_moves';
    protected $fillable = ['game_id','mover','x','y','result'];
    public function game() { return $this->belongsTo(BattleshipGame::class,'game_id'); }
}
