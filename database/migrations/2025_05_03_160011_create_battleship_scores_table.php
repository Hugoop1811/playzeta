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
        Schema::create('battleship_scores', function (Blueprint $table) {
            $table->id();
            $table->foreignId('game_id')->constrained('battleship_games')->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            // Puntos obtenidos
            $table->integer('score');
            // Duración de la partida en segundos
            $table->integer('duration');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('battleship_scores');
    }
};
