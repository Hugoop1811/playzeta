<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class VolumeController extends Controller
{
    /**
     * Actualiza el volumen de la música de fondo (0.0–1.0) en la sesión.
     */
    public function update(Request $request)
    {
        $data = $request->validate([
            'volume' => 'required|numeric|between:0,1',
        ]);

        // Guardamos en sesión
        session(['battleship_bg_volume' => $data['volume']]);

        return response()->json([
            'ok'     => true,
            'volume' => $data['volume'],
        ]);
    }
}
