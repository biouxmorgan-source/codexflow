<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Demande d'avis du MJ, sur une séance ou sur toute la campagne.
        Schema::create('feedback_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('campaign_id')->constrained()->cascadeOnDelete();
            $table->foreignId('play_session_id')->nullable()->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->boolean('anonymous')->default(true);
            $table->timestamp('closed_at')->nullable();
            $table->timestamps();
        });

        // Une réponse par joueur : une note de 1 à 5 et deux commentaires facultatifs.
        Schema::create('feedback_responses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('feedback_request_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->unsignedTinyInteger('rating');
            $table->text('liked')->nullable();
            $table->text('improve')->nullable();
            $table->timestamps();
            $table->unique(['feedback_request_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('feedback_responses');
        Schema::dropIfExists('feedback_requests');
    }
};
