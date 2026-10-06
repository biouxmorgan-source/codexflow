<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Règles libres : rattachées au jeu (partagées par ses campagnes) ou à une seule campagne.
        Schema::create('rules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('game_system_id')->nullable()->constrained()->cascadeOnDelete();
            $table->foreignId('campaign_id')->nullable()->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->string('category', 100)->nullable();
            $table->text('summary')->nullable();
            $table->text('procedure')->nullable();
            $table->text('gm_notes')->nullable();
            $table->string('source')->nullable();
            $table->string('origin', 20)->default('reference');
            $table->string('status', 20)->default('available');
            $table->string('zone', 10)->default('public');
            $table->timestamps();
            $table->index(['game_system_id', 'title']);
            $table->index(['campaign_id', 'title']);
        });

        DB::statement('ALTER TABLE rules ADD CONSTRAINT rules_single_scope CHECK ((game_system_id IS NULL) <> (campaign_id IS NULL))');

        // Documents téléversés : bibliothèque du jeu, du monde ou d'une campagne.
        Schema::create('documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('game_system_id')->nullable()->constrained()->cascadeOnDelete();
            $table->foreignId('world_id')->nullable()->constrained()->cascadeOnDelete();
            $table->foreignId('campaign_id')->nullable()->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('zone', 10)->default('gm');
            $table->string('disk', 20);
            $table->string('path');
            $table->string('original_name');
            $table->string('mime_type', 100);
            $table->unsignedBigInteger('size');
            $table->timestamps();
        });

        DB::statement('ALTER TABLE documents ADD CONSTRAINT documents_single_scope CHECK (num_nonnulls(game_system_id, world_id, campaign_id) = 1)');

        Schema::create('rule_tag', function (Blueprint $table) {
            $table->foreignId('rule_id')->constrained()->cascadeOnDelete();
            $table->foreignId('tag_id')->constrained()->cascadeOnDelete();
            $table->primary(['rule_id', 'tag_id']);
        });

        Schema::create('document_tag', function (Blueprint $table) {
            $table->foreignId('document_id')->constrained()->cascadeOnDelete();
            $table->foreignId('tag_id')->constrained()->cascadeOnDelete();
            $table->primary(['document_id', 'tag_id']);
        });

        // Associations explicites qui alimentent le moteur de contexte.
        Schema::create('rule_scene', function (Blueprint $table) {
            $table->foreignId('scene_id')->constrained()->cascadeOnDelete();
            $table->foreignId('rule_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('position')->default(0);
            $table->primary(['scene_id', 'rule_id']);
        });

        Schema::create('document_scene', function (Blueprint $table) {
            $table->foreignId('scene_id')->constrained()->cascadeOnDelete();
            $table->foreignId('document_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('position')->default(0);
            $table->primary(['scene_id', 'document_id']);
        });

        Schema::create('document_entity', function (Blueprint $table) {
            $table->foreignId('entity_id')->constrained()->cascadeOnDelete();
            $table->foreignId('document_id')->constrained()->cascadeOnDelete();
            $table->timestamps();
            $table->primary(['entity_id', 'document_id']);
        });

        Schema::create('document_rule', function (Blueprint $table) {
            $table->foreignId('rule_id')->constrained()->cascadeOnDelete();
            $table->foreignId('document_id')->constrained()->cascadeOnDelete();
            $table->timestamps();
            $table->primary(['rule_id', 'document_id']);
        });

        // Une règle peut être placée dans « À jouer ».
        Schema::table('to_play_items', function (Blueprint $table) {
            $table->foreignId('rule_id')->nullable()->after('scene_id')->constrained()->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('to_play_items', fn (Blueprint $table) => $table->dropConstrainedForeignId('rule_id'));
        Schema::dropIfExists('document_rule');
        Schema::dropIfExists('document_entity');
        Schema::dropIfExists('document_scene');
        Schema::dropIfExists('rule_scene');
        Schema::dropIfExists('document_tag');
        Schema::dropIfExists('rule_tag');
        Schema::dropIfExists('documents');
        Schema::dropIfExists('rules');
    }
};
