<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Champs nommés par le MJ pour un jeu (caractéristiques, compétences, capacités…).
        // entity_type_id null : le champ s'applique à tous les types de fiche.
        Schema::create('field_definitions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('game_system_id')->constrained()->cascadeOnDelete();
            $table->foreignId('entity_type_id')->nullable()->constrained()->cascadeOnDelete();
            $table->string('group', 100)->nullable();
            $table->string('name', 100);
            $table->string('type', 20);
            $table->jsonb('options')->nullable();
            $table->string('zone')->default('public');
            $table->unsignedInteger('position')->default(0);
            $table->timestamps();
            $table->unique(['game_system_id', 'entity_type_id', 'name'])->nullsNotDistinct();
        });

        // Valeurs indexées par identifiant de définition : renommer un champ ne perd rien.
        Schema::table('entities', function (Blueprint $table) {
            $table->jsonb('field_values')->default('{}');
        });
    }

    public function down(): void
    {
        Schema::table('entities', fn (Blueprint $table) => $table->dropColumn('field_values'));
        Schema::dropIfExists('field_definitions');
    }
};
