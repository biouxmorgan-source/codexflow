<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Feuilles de personnage personnelles : des personnages joués hors de SagaWyn, visibles
        // de leur seul propriétaire. Sans campagne ni jeu : les champs sont libres (fields).
        Schema::create('personal_characters', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('game')->nullable();
            $table->string('table_name')->nullable();
            $table->jsonb('fields')->default('[]');
            $table->text('notes')->nullable();
            $table->string('portrait_path')->nullable();
            $table->unsignedBigInteger('portrait_size')->nullable();
            $table->string('sheet_path')->nullable();
            $table->string('sheet_name')->nullable();
            $table->unsignedBigInteger('sheet_size')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'name']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('personal_characters');
    }
};
