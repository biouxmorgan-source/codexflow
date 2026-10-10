<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Bibliothèque sonore d'une campagne : musiques et ambiances lancées pendant la partie.
        Schema::create('audio_tracks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('campaign_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->boolean('loop')->default(true);
            $table->string('disk', 20);
            $table->string('path');
            $table->string('original_name');
            $table->string('mime_type', 100);
            $table->unsignedBigInteger('size');
            $table->timestamps();
        });

        Schema::create('audio_track_tag', function (Blueprint $table) {
            $table->foreignId('audio_track_id')->constrained()->cascadeOnDelete();
            $table->foreignId('tag_id')->constrained()->cascadeOnDelete();
            $table->primary(['audio_track_id', 'tag_id']);
        });

        Schema::create('audio_track_scene', function (Blueprint $table) {
            $table->foreignId('scene_id')->constrained()->cascadeOnDelete();
            $table->foreignId('audio_track_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('position')->default(0);
            $table->primary(['scene_id', 'audio_track_id']);
        });

        // Ce que joue l'écran de table : {track, volume, playing, key}.
        Schema::table('campaigns', function (Blueprint $table) {
            $table->jsonb('table_audio')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('campaigns', fn (Blueprint $table) => $table->dropColumn('table_audio'));
        Schema::dropIfExists('audio_track_scene');
        Schema::dropIfExists('audio_track_tag');
        Schema::dropIfExists('audio_tracks');
    }
};
