<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Réponse d'une IA collée par le MJ, et les propositions qu'elle contient.
        // Rien ne touche la campagne tant que le MJ n'a pas accepté une proposition.
        Schema::create('ai_analyses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('campaign_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('play_session_id')->nullable()->constrained()->nullOnDelete();
            $table->text('response');
            $table->timestamps();
        });

        Schema::create('ai_suggestions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ai_analysis_id')->constrained()->cascadeOnDelete();
            $table->string('kind', 20);
            $table->jsonb('payload');
            $table->string('status', 10)->default('pending');
            $table->timestamps();

            $table->index(['ai_analysis_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ai_suggestions');
        Schema::dropIfExists('ai_analyses');
    }
};
