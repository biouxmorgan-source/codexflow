<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Séance de jeu (« session » est réservé par Laravel, d'où play_sessions).
        Schema::create('play_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('campaign_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('number');
            $table->string('title')->nullable();
            $table->foreignId('current_scene_id')->nullable()->constrained('scenes')->nullOnDelete();
            $table->timestamp('started_at');
            $table->timestamp('ended_at')->nullable();
            $table->timestamps();
            $table->unique(['campaign_id', 'number']);
        });

        // Une seule séance ouverte à la fois par campagne.
        DB::statement('CREATE UNIQUE INDEX play_sessions_one_open ON play_sessions (campaign_id) WHERE ended_at IS NULL');

        // Notes rapides horodatées, rattachées à la scène en cours au moment de l'écriture.
        Schema::create('session_notes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('play_session_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('scene_id')->nullable()->constrained()->nullOnDelete();
            $table->text('body');
            $table->timestamps();
        });

        // Fiches que le MJ garde sous les yeux d'une séance à l'autre.
        Schema::create('campaign_pins', function (Blueprint $table) {
            $table->foreignId('campaign_id')->constrained()->cascadeOnDelete();
            $table->foreignId('entity_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('position')->default(0);
            $table->timestamps();
            $table->primary(['campaign_id', 'entity_id']);
        });

        // « À jouer » : ce que le MJ veut placer, pour une scène précise ou dès que possible.
        Schema::create('to_play_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('campaign_id')->constrained()->cascadeOnDelete();
            $table->foreignId('scene_id')->nullable()->constrained()->cascadeOnDelete();
            $table->string('body', 500);
            $table->timestamp('done_at')->nullable();
            $table->unsignedInteger('position')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('to_play_items');
        Schema::dropIfExists('campaign_pins');
        Schema::dropIfExists('session_notes');
        Schema::dropIfExists('play_sessions');
    }
};
