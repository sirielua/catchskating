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
        Schema::create('game_sessions_players', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('session_id');
            $table->unsignedBigInteger('player_id')->nullable();
            $table->string('name');
            $table->string('condition', 36);
            $table->unsignedTinyInteger('catching_streak')->default(0);
            $table->unsignedTinyInteger('running_streak')->default(0);
            $table->unsignedTinyInteger('resting_streak')->default(0);
            $table->timestamps();
            
            $table->foreign('session_id')
                ->references('id')
                ->on('game_sessions')
                ->cascadeOnDelete();
            
            $table->foreign('player_id')
                ->references('id')
                ->on('players')
                ->nullOnDelete();
            
            $table->unique(['session_id', 'player_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('game_sessions_players');
    }
};
