<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Étiquettes de rangement du MJ, propres à son compte.
        Schema::create('tags', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('name', 60);
            $table->timestamps();
        });

        DB::statement('CREATE UNIQUE INDEX tags_user_id_lower_name_unique ON tags (user_id, lower(name))');

        Schema::create('entity_tag', function (Blueprint $table) {
            $table->foreignId('entity_id')->constrained()->cascadeOnDelete();
            $table->foreignId('tag_id')->constrained()->cascadeOnDelete();
            $table->primary(['entity_id', 'tag_id']);
        });

        // Relation libre et orientée : « Aldric » travaille pour « la Guilde » (inverse : emploie).
        // campaign_id null : relation du monde, visible dans toutes ses campagnes.
        Schema::create('entity_relations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('from_entity_id')->constrained('entities')->cascadeOnDelete();
            $table->foreignId('to_entity_id')->constrained('entities')->cascadeOnDelete();
            $table->foreignId('campaign_id')->nullable()->constrained()->cascadeOnDelete();
            $table->string('label', 100);
            $table->string('reverse_label', 100)->nullable();
            $table->string('zone')->default('public');
            $table->timestamps();
            $table->index('from_entity_id');
            $table->index('to_entity_id');
        });

        DB::statement('ALTER TABLE entity_relations ADD CONSTRAINT entity_relations_distinct CHECK (from_entity_id <> to_entity_id)');
    }

    public function down(): void
    {
        Schema::dropIfExists('entity_relations');
        Schema::dropIfExists('entity_tag');
        Schema::dropIfExists('tags');
    }
};
