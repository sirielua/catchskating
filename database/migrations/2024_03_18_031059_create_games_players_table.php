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
        Schema::create('games_players', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('game_id');
            $table->unsignedBigInteger('player_id')->nullable();
            $table->unsignedTinyInteger('role');
            $table->string('name');
            
            $table->timestamps();
            
            $table->foreign('game_id')
                ->references('id')
                ->on('games')
                ->cascadeOnDelete();
            
            $table->foreign('player_id')
                ->references('id')
                ->on('players')
                ->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('games_players');
    }
};
