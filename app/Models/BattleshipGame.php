<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BattleshipGame extends Model
{
    protected $table = 'battleship_games';
    protected $fillable = ['user_id','opponent_id','mode','difficulty','status','turn'];

    public function user()       { return $this->belongsTo(User::class); }
    public function opponent()   { return $this->belongsTo(User::class,'opponent_id'); }
    public function boards()     { return $this->hasMany(BattleshipBoard::class); }
    public function moves()      { return $this->hasMany(BattleshipMove::class); }
    public function score()      { return $this->hasOne(BattleshipScore::class); }
}
