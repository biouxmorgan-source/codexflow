<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Prétiré du monde dont la fiche du personnage est une copie : il n'est plus proposé une seconde fois.
        Schema::table('player_characters', function (Blueprint $table) {
            $table->foreignId('source_entity_id')->nullable()->after('entity_id')->constrained('entities')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('player_characters', function (Blueprint $table) {
            $table->dropConstrainedForeignId('source_entity_id');
        });
    }
};
