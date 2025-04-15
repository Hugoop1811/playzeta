<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DailyChallenge extends Model
{
    protected $fillable = ['date', 'word', 'winner_user_id'];

}
