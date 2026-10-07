<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Personnage au nom duquel un joueur écrit : affiché dans la discussion de groupe.
        Schema::table('messages', function (Blueprint $table) {
            $table->foreignId('sender_character_id')->nullable()->after('sender_id')->constrained('player_characters')->nullOnDelete();
        });

        // Messages déjà envoyés par un joueur : ils l'étaient depuis la conversation de son personnage.
        DB::statement('update messages set sender_character_id = player_character_id
            where player_character_id is not null
            and sender_id = (select user_id from player_characters where player_characters.id = messages.player_character_id)');
    }

    public function down(): void
    {
        Schema::table('messages', fn (Blueprint $table) => $table->dropConstrainedForeignId('sender_character_id'));
    }
};
