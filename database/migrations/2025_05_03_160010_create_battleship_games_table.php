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
        Schema::create('battleship_games', function (Blueprint $table) {
            $table->id();
            // Quién inicia la partida
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            // Para PVP: segundo jugador (puede ser null en IA)
            $table->foreignId('opponent_id')->nullable()->constrained('users')->onDelete('cascade');
            // Modo: IA o PVP
            $table->enum('mode', ['IA', 'PVP']);
            // Dificultad (solo para IA)
            $table->enum('difficulty', ['easy', 'medium', 'hard'])->nullable();
            // Estado: setup (colocando barcos), playing, finished
            $table->enum('status', ['setup', 'playing', 'finished'])->default('setup');
            // Turno actual: player (tú) u opponent
            $table->enum('turn', ['player', 'opponent'])->default('player');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('battleship_games');
    }
};
