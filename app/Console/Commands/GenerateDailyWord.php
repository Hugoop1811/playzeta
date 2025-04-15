<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Word;
use App\Models\DailyChallenge;

class GenerateDailyWord extends Command
{
    protected $signature = 'wordle:generate-daily-word';
    protected $description = 'Genera una palabra diaria aleatoria de 5 letras como reto del día';

    public function handle()
    {
        $today = now()->toDateString();

        // Si ya existe un reto para hoy, no hacemos nada
        if (DailyChallenge::where('date', $today)->exists()) {
            $this->info('Ya hay una palabra generada para hoy.');
            return;
        }

        // Elegir palabra aleatoria de la tabla words
        $word = Word::inRandomOrder()->first();

        if (!$word) {
            $this->error('No hay palabras disponibles en la base de datos.');
            return;
        }

        // Guardarla como el reto del día
        DailyChallenge::create([
            'word' => $word->text,
            'date' => $today
        ]);

        $this->info("Palabra del día generada: {$word->text}");
    }
}

