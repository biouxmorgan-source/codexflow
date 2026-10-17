<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Lancers de dés à la table : les lancers (libres ou en réponse au MJ), les demandes du MJ à toute
 * la table ou à certains personnages, et les lancers prêts à l'emploi de la campagne.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('dice_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('campaign_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('label', 100);
            $table->jsonb('formula');
            // table : chacun voit les résultats ; gm : seuls le MJ et le lanceur.
            $table->string('visibility', 10)->default('table');
            // Personnages à qui le MJ demande de lancer, fixés au moment de la demande.
            $table->jsonb('character_ids');
            $table->timestamp('closed_at')->nullable();
            $table->timestamps();
        });

        Schema::create('dice_rolls', function (Blueprint $table) {
            $table->id();
            $table->foreignId('campaign_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('player_character_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('dice_request_id')->nullable()->constrained()->nullOnDelete();
            $table->string('label', 100)->nullable();
            $table->jsonb('formula');
            $table->jsonb('result');
            $table->string('visibility', 10)->default('table');
            $table->timestamps();
            $table->index(['campaign_id', 'created_at']);
        });

        Schema::table('campaigns', function (Blueprint $table) {
            // [{id, label, formula}] : les lancers que le MJ prépare pour sa table.
            $table->jsonb('dice_presets')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('campaigns', function (Blueprint $table) {
            $table->dropColumn('dice_presets');
        });
        Schema::dropIfExists('dice_rolls');
        Schema::dropIfExists('dice_requests');
    }
};
