<?php

namespace App\Http\Controllers;

use App\Models\Word;
use App\Models\DailyChallenge;
use Illuminate\Http\Request;

class WordleController extends Controller
{
    public function index()
    {
        $challenge = DailyChallenge::firstOrCreate(
            ['date' => today()],
            ['word' => 'LARVA'] // palabra fija por ahora
        );

        return view('games.wordle', ['challenge' => $challenge]);
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

        // Validación: debe existir en la base de datos
        if (!Word::whereRaw("UPPER(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(text, 'Á','A'),'É','E'),'Í','I'),'Ó','O'),'Ú','U'),'Ü','U')) = ?", [$guess])->exists()) {
            return response()->json([
                'error' => 'La palabra no es válida',
            ], 422);
        }
        

        $challenge = DailyChallenge::where('date', today())->first();
        $target = $this->quitarTildes($challenge->word);

        // Comparación letra por letra
        $result = [];
        $targetLetters = str_split($target);
        $guessLetters = str_split($guess);
        $used = [];

        // 1. Verdes
        for ($i = 0; $i < 5; $i++) {
            if ($guessLetters[$i] === $targetLetters[$i]) {
                $result[$i] = ['letter' => $guessLetters[$i], 'color' => 'green'];
                $used[$i] = true;
            } else {
                $result[$i] = ['letter' => $guessLetters[$i], 'color' => 'gray'];
            }
        }

        // 2. Amarillos
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
}