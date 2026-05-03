<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Ajoute toutes les contraintes de clés étrangères.
 *
 * Cette migration est isolée pour éviter le problème classique des FKs
 * qui pointent vers des tables pas encore créées au moment où elles
 * sont déclarées (ordre des migrations).
 *
 * À ce stade, toutes les tables existent : on peut câbler proprement.
 */
return new class extends Migration
{
    public function up(): void
    {
        // players → game_sessions
        Schema::table('players', function (Blueprint $table) {
            $table->foreign('game_session_id')
                  ->references('id')->on('game_sessions')
                  ->onDelete('cascade');
        });

        // game_sessions → scenes (current_scene_id)
        Schema::table('game_sessions', function (Blueprint $table) {
            $table->foreign('current_scene_id')
                  ->references('id')->on('scenes')
                  ->onDelete('set null');
        });

        // choices → scenes (parent + next)
        Schema::table('choices', function (Blueprint $table) {
            $table->foreign('scene_id')
                  ->references('id')->on('scenes')
                  ->onDelete('cascade');

            $table->foreign('next_scene_id')
                  ->references('id')->on('scenes')
                  ->onDelete('set null');
        });

        // votes → players, game_sessions, scenes, choices
        Schema::table('votes', function (Blueprint $table) {
            $table->foreign('player_id')
                  ->references('id')->on('players')
                  ->onDelete('cascade');

            $table->foreign('game_session_id')
                  ->references('id')->on('game_sessions')
                  ->onDelete('cascade');

            $table->foreign('scene_id')
                  ->references('id')->on('scenes')
                  ->onDelete('cascade');

            $table->foreign('choice_id')
                  ->references('id')->on('choices')
                  ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::table('votes', function (Blueprint $table) {
            $table->dropForeign(['player_id']);
            $table->dropForeign(['game_session_id']);
            $table->dropForeign(['scene_id']);
            $table->dropForeign(['choice_id']);
        });

        Schema::table('choices', function (Blueprint $table) {
            $table->dropForeign(['scene_id']);
            $table->dropForeign(['next_scene_id']);
        });

        Schema::table('game_sessions', function (Blueprint $table) {
            $table->dropForeign(['current_scene_id']);
        });

        Schema::table('players', function (Blueprint $table) {
            $table->dropForeign(['game_session_id']);
        });
    }
};
