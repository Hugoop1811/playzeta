<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Word;

class ImportWordsFromFile extends Command
{
    protected $signature = 'wordle:import-words';
    protected $description = 'Importa palabras válidas de 5 letras desde palabras.txt';

    public function handle()
    {
        $path = storage_path('app/palabras.txt');

        if (!file_exists($path)) {
            $this->error("El archivo palabras.txt no existe en storage/app.");
            return;
        }

        $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        $count = 0;
        $tildeCount = 0;
        $enieCount = 0;
        $rejected = 0;

        foreach ($lines as $line) {
            $clean = trim(mb_convert_encoding($line, 'UTF-8', 'auto'));
        
            // Elimina caracteres invisibles (BOM, saltos raros, etc.)
            $clean = preg_replace('/[\x00-\x1F\x7F]/u', '', $clean);
        
            // Convertimos a mayúsculas y normalizamos tildes y ñ
            $word = strtr(mb_strtoupper($clean, 'UTF-8'), [
                'á' => 'Á', 'é' => 'É', 'í' => 'Í', 'ó' => 'Ó', 'ú' => 'Ú', 'ü' => 'Ü', 'ñ' => 'Ñ'
            ]);
        
            if (mb_strlen($word, 'UTF-8') !== 5 || !preg_match('/^[A-ZÁÉÍÓÚÜÑ]{5}$/u', $word)) {
                $rejected++;
                continue;
            }
        
            if (mb_strpos($word, 'Ñ') !== false) $enieCount++;
            if (preg_match('/[ÁÉÍÓÚÜ]/u', $word)) $tildeCount++;
        
            Word::firstOrCreate(['text' => $word]);
            $count++;
        }
        

        $this->info("✅ Se importaron $count palabras válidas de 5 letras.");
        $this->info("➡️  Con tilde: $tildeCount | con Ñ: $enieCount | rechazadas: $rejected");
    }
}
