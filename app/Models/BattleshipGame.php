<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\BattleshipBoard;
use App\Models\BattleshipMove;
use App\Models\BattleshipScore;
use App\Models\User;

class BattleshipGame extends Model
{
    protected $table = 'battleship_games';
    protected $fillable = ['user_id','opponent_id','mode','difficulty','status','turn'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function opponent()
    {
        return $this->belongsTo(User::class, 'opponent_id');
    }

    public function boards()
    {
        // indicamos que la FK en battleship_boards es 'game_id'
        return $this->hasMany(BattleshipBoard::class, 'game_id');
    }

    public function moves()
    {
        // idem para battleship_moves
        return $this->hasMany(BattleshipMove::class, 'game_id');
    }

    public function score()
    {
        // idem para battleship_scores
        return $this->hasOne(BattleshipScore::class, 'game_id');
    }
}
