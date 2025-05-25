<?php

namespace App\Http\Controllers;

use App\Models\WordleTimeScore;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class WordleTimeScoreController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'score' => 'required|integer|min:0',
        ]);

        if (Auth::check()) {
            WordleTimeScore::create([
                'user_id' => Auth::id(),
                'score' => $request->score,
            ]);
        }

        return response()->json(['success' => true]);
    }
    public function historial()
{
    $puntuaciones = WordleTimeScore::where('user_id', auth()->id())
        ->orderBy('created_at', 'desc')
        ->get();

    return view('games.wordle.time_history', compact('puntuaciones'));
}
public function leaderboard()
{
    $topScores = \App\Models\WordleTimeScore::with('user')
        ->orderByDesc('score')
        ->limit(50)
        ->get();

    return view('games.wordle.leaderboard', compact('topScores'));
}


}
