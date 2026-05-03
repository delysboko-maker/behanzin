<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('game_sessions', function (Blueprint $table) {
            $table->id();
            $table->string('code', 8)->unique();
            $table->string('master_name', 60);

            // Token secret pour authentifier le maître de jeu (dashboard)
            $table->string('master_token', 64)->unique();

            $table->unsignedInteger('max_players')->default(6);

            // Scène courante (null = pas encore commencé)
            $table->unsignedBigInteger('current_scene_id')->nullable();

            // Statut : waiting | active | finished
            $table->enum('status', ['waiting', 'active', 'finished'])->default('waiting');

            // Résultat final
            $table->string('ending_type', 20)->nullable();   // resistance | exile | sacrifice
            $table->string('ending_title', 120)->nullable();
            $table->string('ending_subtitle', 255)->nullable();
            $table->text('ending_text')->nullable();

            $table->timestamps();

            // Index utile
            $table->index('status');

            // Les FKs sont créées dans la migration 2026_04_30_040000_add_foreign_keys
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('game_sessions');
    }
};
