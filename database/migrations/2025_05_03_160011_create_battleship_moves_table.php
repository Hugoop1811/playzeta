<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('battleship_moves', function (Blueprint $table) {
            $table->id();
            $table->foreignId('game_id')->constrained('battleship_games')->onDelete('cascade');
            // Quién dispara: 'player' o 'opponent'
            $table->enum('mover', ['player', 'opponent']);
            // Coordenadas del disparo
            $table->tinyInteger('x');
            $table->tinyInteger('y');
            // Resultado: 'agua', 'tocado' o 'hundido'
            $table->enum('result', ['agua', 'tocado', 'hundido']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('battleship_moves');
    }
};
