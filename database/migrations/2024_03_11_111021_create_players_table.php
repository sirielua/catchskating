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
        Schema::create('players', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->tinyInteger('endurance')->unsigned()->nullable();
            $table->tinyInteger('agility')->unsigned()->nullable();
            $table->tinyInteger('tactics')->unsigned()->nullable();
            $table->integer('mmr')->unsigned();
            $table->integer('points')->unsigned()->default(0);
            $table->integer('games_total')->unsigned()->default(0);
            $table->integer('games_as_catcher')->unsigned()->default(0);
            $table->integer('games_as_runner')->unsigned()->default(0);
            $table->integer('wins_total')->unsigned()->default(0);
            $table->integer('wins_as_catcher')->unsigned()->default(0);
            $table->integer('wins_as_runner')->unsigned()->default(0);
            $table->integer('loses_total')->unsigned()->default(0);
            $table->integer('loses_as_catcher')->unsigned()->default(0);
            $table->integer('loses_as_runner')->unsigned()->default(0);
            $table->integer('time_catching')->unsigned()->default(0);
            $table->integer('time_running')->unsigned()->default(0);
            $table->timestamp('last_played_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('players');
    }
};
