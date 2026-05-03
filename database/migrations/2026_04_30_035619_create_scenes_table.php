<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('scenes', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('act')->default(1);       // 1, 2, 3, 4
            $table->string('act_title', 100)->default('');
            $table->string('year', 20)->default('');          // "1890"
            $table->string('title', 120);
            $table->text('content');                          // Texte narratif
            $table->string('quote', 400)->nullable();
            $table->string('quote_author', 80)->nullable();
            $table->string('image_path', 255)->nullable();
            $table->string('question', 255)->nullable();      // Question de vote
            $table->boolean('is_ending')->default(false);     // Scène finale ?

            // Type d'ending pour les scènes finales (null sinon)
            //   resistance | exile | sacrifice
            $table->string('ending_type', 20)->nullable();

            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index('sort_order');
            $table->index('is_ending');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('scenes');
    }
};
