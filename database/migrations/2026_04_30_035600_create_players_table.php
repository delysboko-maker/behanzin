<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('players', function (Blueprint $table) {
            $table->id();
            $table->string('pseudo', 32);
            $table->unsignedBigInteger('game_session_id');
            $table->boolean('is_active')->default(true);
            $table->timestamp('last_seen_at')->nullable();
            $table->timestamps();

            // Index pour les recherches fréquentes
            $table->index('game_session_id');

            // Un même pseudo ne peut apparaître qu'une fois par session
            $table->unique(['pseudo', 'game_session_id']);

            // Les FKs sont créées dans la migration 2026_04_30_040000_add_foreign_keys
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('players');
    }
};
