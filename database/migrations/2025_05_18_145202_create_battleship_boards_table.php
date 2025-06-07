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
        Schema::create('battleship_boards', function (Blueprint $table) {
            $table->id();
            $table->foreignId('game_id')
                ->constrained('battleship_games')
                ->cascadeOnDelete();
            // 'player' o 'opponent'
            $table->enum('owner', ['player', 'opponent']);
            // JSON de barcos y de impactos
            $table->json('ships')->nullable();
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
