<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BattleshipBoard extends Model
{
    protected $table = 'battleship_boards';
    protected $fillable = ['game_id','owner','ships','hits'];
    protected $casts    = ['ships'=>'array','hits'=>'array'];
    public function game() { return $this->belongsTo(BattleshipGame::class,'game_id'); }
}
