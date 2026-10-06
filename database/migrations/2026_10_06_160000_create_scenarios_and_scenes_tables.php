<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('scenarios', function (Blueprint $table) {
            $table->id();
            $table->foreignId('campaign_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->text('summary')->nullable();
            $table->unsignedInteger('position')->default(0);
            $table->timestamps();
        });

        // Le chapitre (acte, partie…) est un simple libellé facultatif qui regroupe les scènes.
        Schema::create('scenes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('scenario_id')->constrained()->cascadeOnDelete();
            $table->string('chapter', 100)->nullable();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('status', 20)->default('planned');
            $table->unsignedInteger('position')->default(0);
            $table->timestamps();
        });

        // Fiches présentes ou utiles dans une scène, avec une précision libre (« caché à la cave »).
        Schema::create('scene_entity', function (Blueprint $table) {
            $table->foreignId('scene_id')->constrained()->cascadeOnDelete();
            $table->foreignId('entity_id')->constrained()->cascadeOnDelete();
            $table->string('note', 150)->nullable();
            $table->unsignedInteger('position')->default(0);
            $table->primary(['scene_id', 'entity_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('scene_entity');
        Schema::dropIfExists('scenes');
        Schema::dropIfExists('scenarios');
    }
};
