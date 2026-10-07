<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Préférences d'affichage : thème, couleur d'accent, taille du texte.
            $table->jsonb('preferences')->nullable();
            // Dernière version dont l'utilisateur a vu les nouveautés (« Quoi de neuf »).
            $table->string('last_seen_version', 20)->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['preferences', 'last_seen_version']);
        });
    }
};
