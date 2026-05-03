<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('choices', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('scene_id');
            $table->string('text', 400);
            // Scène vers laquelle ce choix mène (null = fin)
            $table->unsignedBigInteger('next_scene_id')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index('scene_id');
            $table->index('next_scene_id');

            // Les FKs sont créées dans la migration 2026_04_30_040000_add_foreign_keys
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('choices');
    }
};
