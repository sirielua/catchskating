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
        Schema::create('games', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('session_id');
            $table->string('status', 36)->index();
            $table->unsignedTinyInteger('catchers_count');
            $table->unsignedTinyInteger('runners_count');
            $table->timestamp('started_at')->nullable();
            $table->timestamp('stopped_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->unsignedInteger('duration')->default(0);
            $table->unsignedInteger('pause_duration')->default(0);
            $table->unsignedTinyInteger('winner')->nullable();
            $table->timestamps();
            
            $table->foreign('session_id')
                ->references('id')
                ->on('game_sessions')
                ->restrictOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('games');
    }
};
