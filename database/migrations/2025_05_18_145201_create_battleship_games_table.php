<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('battleship_games', function (Blueprint $table) {
            $table->id();
            // Quien crea la partida; nullable para IA guest
            $table->foreignId('user_id')
                ->nullable()
                ->constrained()
                ->nullOnDelete();
            // En PVP, se rellenará al unirse el rival
            $table->foreignId('opponent_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();
            // Modo de juego: IA o PVP
            $table->enum('mode', ['IA', 'PVP']);
            // Dificultad sólo válida para IA
            $table->enum('difficulty', ['easy', 'medium', 'hard'])
                ->nullable();
            // Estado del flujo
            $table->enum('status', ['setup', 'playing', 'finished'])
                ->default('setup');
            // Turno actual: player|opponent
            $table->enum('turn', ['player', 'opponent'])
                ->default('player');
            // Token único para invitaciones PVP
            $table->string('invite_token', 64)
                ->nullable()
                ->unique()
                ->comment('Token para invitar al rival en PVP');
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
