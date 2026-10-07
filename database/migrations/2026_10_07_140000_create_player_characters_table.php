<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Champ que le joueur peut modifier sur la fiche de son personnage (zone publique seulement).
        Schema::table('field_definitions', function (Blueprint $table) {
            $table->boolean('player_editable')->default(false);
        });

        // Personnage joueur : une fiche de la campagne confiée à un joueur, avec sa feuille PDF.
        Schema::create('player_characters', function (Blueprint $table) {
            $table->id();
            $table->foreignId('campaign_id')->constrained()->cascadeOnDelete();
            $table->foreignId('entity_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->boolean('is_active')->default(true);
            $table->boolean('locked')->default(false);
            $table->string('sheet_path')->nullable();
            $table->string('sheet_name')->nullable();
            $table->unsignedBigInteger('sheet_size')->nullable();
            $table->timestamps();
            $table->unique(['campaign_id', 'entity_id']);
        });

        // Un seul personnage actif par joueur et par campagne.
        DB::statement('create unique index player_characters_one_active on player_characters (campaign_id, user_id) where is_active and user_id is not null');
    }

    public function down(): void
    {
        Schema::dropIfExists('player_characters');

        Schema::table('field_definitions', function (Blueprint $table) {
            $table->dropColumn('player_editable');
        });
    }
};
