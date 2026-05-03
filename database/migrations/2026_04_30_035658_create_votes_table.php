<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('votes', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('player_id');
            $table->unsignedBigInteger('game_session_id');
            $table->unsignedBigInteger('scene_id');
            $table->unsignedBigInteger('choice_id');
            $table->timestamps();

            // Index pour les agrégats fréquents
            $table->index(['game_session_id', 'scene_id']);
            $table->index('choice_id');

            // Un joueur ne vote qu'une fois par scène par session
            $table->unique(['player_id', 'game_session_id', 'scene_id']);

            // Les FKs sont créées dans la migration 2026_04_30_040000_add_foreign_keys
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('votes');
    }
};
