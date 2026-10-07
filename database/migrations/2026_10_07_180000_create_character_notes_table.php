<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Notes d'un joueur, rattachées à son personnage et à la séance en cours.
        // visibility : private (moi seul), gm (moi et le MJ), players (+ personnages choisis), group (toute la table).
        Schema::create('character_notes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('player_character_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('play_session_id')->nullable()->constrained()->nullOnDelete();
            $table->string('visibility', 10)->default('gm');
            $table->text('body');
            $table->timestamps();
        });

        Schema::create('character_note_shares', function (Blueprint $table) {
            $table->foreignId('character_note_id')->constrained()->cascadeOnDelete();
            $table->foreignId('player_character_id')->constrained()->cascadeOnDelete();
            $table->primary(['character_note_id', 'player_character_id']);
        });

        // Intention d'un joueur (« À jouer ») : elle apparaît chez le MJ avec le nom du personnage.
        Schema::table('to_play_items', function (Blueprint $table) {
            $table->foreignId('player_character_id')->nullable()->constrained()->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('to_play_items', fn (Blueprint $table) => $table->dropConstrainedForeignId('player_character_id'));
        Schema::dropIfExists('character_note_shares');
        Schema::dropIfExists('character_notes');
    }
};
