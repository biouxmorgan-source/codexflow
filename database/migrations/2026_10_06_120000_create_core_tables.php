<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Jeu générique : aucune règle métier, seulement un nom et des documents de référence.
        Schema::create('game_systems', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('image_path')->nullable();
            $table->timestamps();
        });

        Schema::create('worlds', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('image_path')->nullable();
            $table->timestamps();
        });

        Schema::create('campaigns', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('game_system_id')->constrained()->restrictOnDelete();
            $table->foreignId('world_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('status')->default('active');
            $table->timestamp('archived_at')->nullable();
            $table->timestamps();
        });

        // Le rôle (MJ ou joueur) est porté par l'appartenance à une campagne, pas par le compte.
        Schema::create('campaign_memberships', function (Blueprint $table) {
            $table->id();
            $table->foreignId('campaign_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('role');
            $table->timestamps();
            $table->unique(['campaign_id', 'user_id']);
        });

        // user_id null = type standard, partagé par tous les comptes.
        Schema::create('entity_types', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->cascadeOnDelete();
            $table->string('key')->nullable()->unique();
            $table->string('name');
            $table->timestamps();
        });

        // Une entité appartient soit à un monde (réutilisable), soit à une seule campagne.
        // Zone publique : summary, description. Zone MJ : gm_notes.
        Schema::create('entities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('world_id')->nullable()->constrained()->cascadeOnDelete();
            $table->foreignId('campaign_id')->nullable()->constrained()->cascadeOnDelete();
            $table->foreignId('entity_type_id')->constrained()->restrictOnDelete();
            $table->string('name');
            $table->text('summary')->nullable();
            $table->text('description')->nullable();
            $table->text('gm_notes')->nullable();
            $table->string('image_path')->nullable();
            $table->timestamps();
            $table->index(['world_id', 'name']);
            $table->index(['campaign_id', 'name']);
        });

        DB::statement('ALTER TABLE entities ADD CONSTRAINT entities_single_scope CHECK ((world_id IS NULL) <> (campaign_id IS NULL))');

        // État d'une entité de monde propre à une campagne (ex. PNJ mort ici, vivant ailleurs).
        Schema::create('campaign_entity_states', function (Blueprint $table) {
            $table->id();
            $table->foreignId('campaign_id')->constrained()->cascadeOnDelete();
            $table->foreignId('entity_id')->constrained()->cascadeOnDelete();
            $table->string('status', 60)->nullable();
            $table->text('gm_notes')->nullable();
            $table->jsonb('overrides')->default('{}');
            $table->timestamps();
            $table->unique(['campaign_id', 'entity_id']);
        });

        $now = now();
        DB::table('entity_types')->insert(collect([
            'character' => 'Personnage',
            'place' => 'Lieu',
            'organization' => 'Organisation',
            'item' => 'Objet',
            'creature' => 'Créature',
            'document' => 'Document',
        ])->map(fn (string $name, string $key) => [
            'key' => $key,
            'name' => $name,
            'created_at' => $now,
            'updated_at' => $now,
        ])->values()->all());
    }

    public function down(): void
    {
        Schema::dropIfExists('campaign_entity_states');
        Schema::dropIfExists('entities');
        Schema::dropIfExists('entity_types');
        Schema::dropIfExists('campaign_memberships');
        Schema::dropIfExists('campaigns');
        Schema::dropIfExists('worlds');
        Schema::dropIfExists('game_systems');
    }
};
