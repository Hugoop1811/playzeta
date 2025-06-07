<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('speedclick_scores', function (Blueprint $table) {
            $table->id();
            $table->string('user_name');
            $table->integer('reaction_time_ms');
            $table->timestamp('created_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('speedclick_scores');
    }
};

