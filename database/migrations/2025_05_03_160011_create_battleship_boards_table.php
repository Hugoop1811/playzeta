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
        Schema::create('battleship_boards', function (Blueprint $table) {
            $table->id();
            // Relación a la partida
            $table->foreignId('game_id')->constrained('battleship_games')->onDelete('cascade');
            // Owner: 'player' o 'opponent'
            $table->enum('owner', ['player', 'opponent']);
            // JSON con posiciones de barcos: e.g. [{size:4,cells:[[0,0],[0,1]…]},…]
            $table->json('ships')->nullable();
            // JSON con casillas donde han disparado: [[3,5],…]
            $table->json('hits')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('battleship_boards');
    }
};
