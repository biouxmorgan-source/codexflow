<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Jetons déplacés par les joueurs sur l'écran partagé : le jeton de son personnage, et ceux que
 * le MJ confie à un joueur (suivant, familier, ennemi contrôlé). Ordre d'initiative montré à la table.
 * Lien invité pour regarder l'écran partagé sans compte.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('map_tokens', function (Blueprint $table) {
            $table->foreignId('controller_id')->nullable()->after('entity_id')->constrained('users')->nullOnDelete();
        });

        Schema::table('campaigns', function (Blueprint $table) {
            // {shown, round, turn, next, entries: [{id, label, entity_id, color, value, ordered, visible}]}
            $table->jsonb('table_initiative')->nullable();
            // Lien invité : regarder l'écran partagé sans compte, en lecture seule, révocable.
            $table->string('table_guest_token', 64)->nullable()->unique();
        });
    }

    public function down(): void
    {
        Schema::table('map_tokens', function (Blueprint $table) {
            $table->dropConstrainedForeignId('controller_id');
        });

        Schema::table('campaigns', function (Blueprint $table) {
            $table->dropColumn(['table_initiative', 'table_guest_token']);
        });
    }
};
