<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Ce que le MJ a révélé ou donné à un personnage : fiche (zone publique), information,
        // objet ou document. Appartient au personnage, pas au joueur.
        Schema::create('character_grants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('player_character_id')->constrained()->cascadeOnDelete();
            $table->string('kind', 20);
            $table->foreignId('entity_id')->nullable()->constrained()->cascadeOnDelete();
            $table->foreignId('document_id')->nullable()->constrained()->cascadeOnDelete();
            $table->string('title')->nullable();
            $table->text('body')->nullable();
            $table->unsignedInteger('quantity')->nullable();
            $table->foreignId('granted_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->index(['player_character_id', 'kind']);
        });

        // Une fiche ou un document n'est révélé qu'une fois à un même personnage.
        DB::statement('create unique index character_grants_entity_once on character_grants (player_character_id, entity_id) where entity_id is not null');
        DB::statement('create unique index character_grants_document_once on character_grants (player_character_id, document_id) where document_id is not null');
    }

    public function down(): void
    {
        Schema::dropIfExists('character_grants');
    }
};
