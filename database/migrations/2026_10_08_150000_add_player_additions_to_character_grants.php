<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('character_grants', function (Blueprint $table) {
            // Connaissance notée ou objet ajouté par le joueur lui-même (et non donné par le MJ).
            $table->boolean('added_by_player')->default(false);
            // Objet ajouté par le joueur : en attente tant que le MJ ne l'a pas validé.
            $table->timestamp('validated_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('character_grants', function (Blueprint $table) {
            $table->dropColumn(['added_by_player', 'validated_at']);
        });
    }
};
