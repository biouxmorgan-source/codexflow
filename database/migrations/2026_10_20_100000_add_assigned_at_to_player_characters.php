<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Date à laquelle le joueur actuel a reçu le personnage : un nouveau joueur ne lit pas
 * la conversation privée de l'ancien avec le MJ.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('player_characters', function (Blueprint $table) {
            $table->timestamp('assigned_at')->nullable()->after('user_id');
        });
    }

    public function down(): void
    {
        Schema::table('player_characters', function (Blueprint $table) {
            $table->dropColumn('assigned_at');
        });
    }
};
