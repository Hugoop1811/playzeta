<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Word;

class WordSeeder extends Seeder
{
    public function run(): void
    {
        $path = storage_path('app/palabras.txt');
        $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

        foreach ($lines as $line) {
            $word = trim(mb_strtoupper($line, 'UTF-8'));

            // Solo palabras de 5 letras, que pueden tener tildes, ñ o ü
            if (mb_strlen($word) === 5 && preg_match('/^[A-ZÁÉÍÓÚÜÑ]{5}$/u', $word)) {
                Word::firstOrCreate(['text' => $word]);
            }
        }
    }
}
