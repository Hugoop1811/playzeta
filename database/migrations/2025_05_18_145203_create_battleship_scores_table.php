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
        Schema::create('battleship_scores', function (Blueprint $table) {
            $table->id();
            $table->foreignId('game_id')
                ->constrained('battleship_games')
                ->cascadeOnDelete();
            $table->foreignId('user_id')
                ->constrained()
                ->cascadeOnDelete();
            $table->integer('score');      // p.ej. 100 puntos por victoria
            $table->integer('duration');   // en segundos
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
