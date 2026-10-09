<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Qui a joué chaque personnage, et quand : rendre un personnage à n'importe lequel de ses anciens
 * joueurs, et lui rendre ses propres échanges privés avec le MJ, sans ceux des joueurs intermédiaires.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('character_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('player_character_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->timestamp('started_at');
            $table->timestamp('ended_at')->nullable();
            $table->index(['player_character_id', 'user_id']);
        });

        // Les personnages joués aujourd'hui ; leur ancien joueur, s'ils sont libres.
        $now = now();
        foreach (DB::table('player_characters')->get(['id', 'user_id', 'previous_user_id', 'assigned_at', 'created_at', 'updated_at']) as $character) {
            if ($character->previous_user_id !== null) {
                DB::table('character_assignments')->insert([
                    'player_character_id' => $character->id,
                    'user_id' => $character->previous_user_id,
                    'started_at' => $character->created_at ?? $now,
                    'ended_at' => $character->assigned_at ?? $character->updated_at ?? $now,
                ]);
            }
            if ($character->user_id !== null) {
                DB::table('character_assignments')->insert([
                    'player_character_id' => $character->id,
                    'user_id' => $character->user_id,
                    'started_at' => $character->assigned_at ?? $character->created_at ?? $now,
                    'ended_at' => null,
                ]);
            }
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('character_assignments');
    }
};
