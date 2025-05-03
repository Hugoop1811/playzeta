<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BattleshipScore extends Model
{
    protected $table = 'battleship_scores';
    protected $fillable = ['game_id','user_id','score','duration'];
    public function game() { return $this->belongsTo(BattleshipGame::class,'game_id'); }
    public function user() { return $this->belongsTo(User::class); }
}
