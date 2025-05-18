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

    // Elegir palabra aleatoria de la tabla words
    $word = Word::inRandomOrder()->first();

    if (!$word) {
        $this->error('No hay palabras disponibles en la base de datos.');
        return;
    }

    // Actualizar o crear el reto del día
    DailyChallenge::updateOrCreate(
        ['date' => $today],
        ['word' => $word->text]
    );

    $this->info("Palabra del día generada: {$word->text}");
}

}

