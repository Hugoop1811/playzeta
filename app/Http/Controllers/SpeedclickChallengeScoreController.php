<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class SpeedclickChallengeScoreController extends Controller
{
    public function store(Request $request)
{
    $reactionTime = $request->input('reaction_time_ms');
    $userName = Auth::check() ? Auth::user()->name : 'Invitado';

    DB::table('speedclick_challenge_scores')->insert([
        'user_name' => $userName,
        'reaction_time_ms' => $reactionTime,
        'created_at' => now()
    ]);

    return response()->json(['success' => true, 'reaction_time_ms' => $reactionTime]);
}


    public function leaderboard()
    {
        $topScores = DB::table('speedclick_challenge_scores')
            ->orderBy('reaction_time_ms', 'asc')
            ->limit(50)
            ->get();

        return view('games.speedclick.challenge_leaderboard', compact('topScores'));

    }
}
