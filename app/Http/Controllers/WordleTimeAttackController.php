<?php

namespace App\Http\Controllers;

use App\Models\Word;
use App\Models\Score;
use Illuminate\Support\Facades\Auth;

class WordleTimeAttackController extends Controller
{
    public function index()
    {
        return view('games.wordle_time_attack');
    }

    public function check(Request $request)
    {
        $word = strtoupper(trim($request->input('guess')));

        if (Word::where('text', $word)->exists()) {
            if (Auth::check()) {
                Score::create([
                    'user_id' => Auth::id(),
                    'points' => 100
                ]);
            }

            return response()->json(['valid' => true, 'points' => 100]);
        }

        return response()->json(['valid' => false]);
    }
}

