<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class SpeedclickScoreController extends Controller
{
    public function store(Request $request)
    {
        $reactionTime = $request->input('reaction_time_ms');

        // Nombre del jugador (si está autenticado, si no "Invitado")
        $userName = Auth::check() ? Auth::user()->name : 'Invitado';

        // Insertar en la base de datos
        DB::table('speedclick_scores')->insert([
            'user_name' => $userName,
            'reaction_time_ms' => $reactionTime,
            'created_at' => now()
        ]);

        return response()->json(['success' => true, 'reaction_time_ms' => $reactionTime]);
    }
    public function leaderboard()
{
    // Obtener las 50 mejores puntuaciones (los tiempos más rápidos)
    $topScores = \DB::table('speedclick_scores')
        ->orderBy('reaction_time_ms', 'asc') // Menor tiempo es mejor
        ->limit(50)
        ->get();

    return view('games.speedclick.leaderboard', compact('topScores'));
}

}
