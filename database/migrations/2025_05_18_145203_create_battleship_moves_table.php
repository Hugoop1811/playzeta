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
        Schema::create('battleship_moves', function (Blueprint $table) {
            $table->id();
            $table->foreignId('game_id')
                ->constrained('battleship_games')
                ->cascadeOnDelete();
            // quién dispara
            $table->enum('shooter', ['player', 'opponent']);
            $table->unsignedTinyInteger('x');
            $table->unsignedTinyInteger('y');
            // resultado: agua|tocado|hundido
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
