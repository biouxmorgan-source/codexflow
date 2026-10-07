<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Une règle n'est consultable par un joueur qu'une fois ouverte à son personnage par le MJ.
        Schema::table('character_grants', function (Blueprint $table) {
            $table->foreignId('rule_id')->nullable()->after('document_id')->constrained()->cascadeOnDelete();
        });

        DB::statement('create unique index character_grants_rule_once on character_grants (player_character_id, rule_id) where rule_id is not null');
    }

    public function down(): void
    {
        DB::statement('drop index if exists character_grants_rule_once');

        Schema::table('character_grants', function (Blueprint $table) {
            $table->dropConstrainedForeignId('rule_id');
        });
    }
};
