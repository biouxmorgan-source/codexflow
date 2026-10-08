<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Par défaut, le MJ valide les échanges entre joueurs ; il peut les autoriser une fois pour toutes.
        Schema::table('campaigns', function (Blueprint $table) {
            $table->boolean('exchanges_need_approval')->default(true);
        });

        // Échange proposé par un joueur, en attente du MJ. Une seule demande à la fois par élément.
        Schema::create('exchange_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('campaign_id')->constrained()->cascadeOnDelete();
            $table->foreignId('character_grant_id')->unique()->constrained()->cascadeOnDelete();
            $table->foreignId('from_character_id')->constrained('player_characters')->cascadeOnDelete();
            $table->foreignId('to_character_id')->constrained('player_characters')->cascadeOnDelete();
            $table->unsignedInteger('quantity')->nullable();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('exchange_requests');

        Schema::table('campaigns', function (Blueprint $table) {
            $table->dropColumn('exchanges_need_approval');
        });
    }
};
