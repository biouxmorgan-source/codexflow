<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Un son sans campagne appartient à la bibliothèque personnelle de son MJ : un seul fichier,
        // ajouté aux campagnes qui en ont besoin.
        Schema::table('audio_tracks', function (Blueprint $table) {
            $table->foreignId('campaign_id')->nullable()->change();
        });

        Schema::create('audio_track_campaign', function (Blueprint $table) {
            $table->foreignId('campaign_id')->constrained()->cascadeOnDelete();
            $table->foreignId('audio_track_id')->constrained()->cascadeOnDelete();
            $table->primary(['campaign_id', 'audio_track_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('audio_track_campaign');
        DB::table('audio_tracks')->whereNull('campaign_id')->delete();

        Schema::table('audio_tracks', function (Blueprint $table) {
            $table->foreignId('campaign_id')->nullable(false)->change();
        });
    }
};
