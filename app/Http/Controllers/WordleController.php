<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\DailyChallenge;
use App\Models\Score;
use App\Models\Word;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class WordleController extends Controller
{
    public function index()
    {
        $challenge = DailyChallenge::firstOrCreate(
            ['date' => today()],
            ['word' => 'LARVA']
        );

        return view('games.wordle.wordle', ['challenge' => $challenge]);
    }

    private function quitarTildes($cadena)
    {
        $originales = ['Á','É','Í','Ó','Ú','Ü','á','é','í','ó','ú','ü'];
        $modificadas = ['A','E','I','O','U','U','A','E','I','O','U','U'];
        return strtr(mb_strtoupper($cadena), array_combine($originales, $modificadas));
    }

    public function check(Request $request)
    {
        $rawGuess = trim($request->input('guess'));
        $guess = $this->quitarTildes($rawGuess);

        if (!Word::whereRaw("UPPER(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(text, 'Á','A'),'É','E'),'Í','I'),'Ó','O'),'Ú','U'),'Ü','U')) = ?", [$guess])->exists()) {
            return response()->json(['error' => 'La palabra no es válida'], 422);
        }

        $challenge = DailyChallenge::where('date', today())->first();
        $target = $this->quitarTildes($challenge->word);

        $result = [];
        $targetLetters = str_split($target);
        $guessLetters = str_split($guess);
        $used = [];

        for ($i = 0; $i < 5; $i++) {
            if ($guessLetters[$i] === $targetLetters[$i]) {
                $result[$i] = ['letter' => $guessLetters[$i], 'color' => 'green'];
                $used[$i] = true;
            } else {
                $result[$i] = ['letter' => $guessLetters[$i], 'color' => 'gray'];
            }
        }

        for ($i = 0; $i < 5; $i++) {
            if ($result[$i]['color'] === 'green') continue;

            for ($j = 0; $j < 5; $j++) {
                if (!isset($used[$j]) && $guessLetters[$i] === $targetLetters[$j]) {
                    $result[$i]['color'] = 'yellow';
                    $used[$j] = true;
                    break;
                }
            }
        }

        return response()->json([
            'correct' => $guess === $target,
            'guess' => $guess,
            'result' => $result,
            'target' => $challenge->word
        ]);
    }

    public function checkTimeMode(Request $request)
    {
        $guess = strtoupper(trim($request->input('guess')));

        return response()->json([
            'valid' => Word::where('text', $guess)->exists()
        ]);
    }

    public function saveTimeScore(Request $request)
    {
        $points = intval($request->input('puntos'));

        if (Auth::check()) {
            Score::create([
                'user_id' => Auth::id(),
                'game' => 'wordle-contrarreloj',
                'points' => $points,
            ]);
        }

        return response()->json(['success' => true]);
    }
   public function getRandomWord()
{
    $word = DB::table('words')->inRandomOrder()->value('text');

    if (!$word) {
        return response()->json(['error' => 'No hay palabras disponibles'], 500);
    }

    return response()->json(['word' => strtoupper($word)]);
}


public function saveTimeAttackScore(Request $request)
{
    if (!Auth::check()) {
        return response()->json(['message' => 'No autenticado, puntuación no guardada.'], 401);
    }

    $request->validate([
        'score' => 'required|integer',
    ]);

    DB::table('time_attack_scores')->insert([
        'user_id' => Auth::id(),
        'score' => $request->score,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    return response()->json(['message' => 'Puntuación guardada']);
}
public function timeAttack()
{
    return view('games.wordle.wordle_time_attack');
}



}
