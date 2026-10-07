<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Journal d'audit : qui, quoi, quand, ancienne et nouvelle valeur. Les anciennes valeurs
        // sont gardées dès maintenant pour permettre une restauration plus tard (V1.x).
        Schema::create('activity_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            // Portée de l'élément modifié, pour retrouver ses entrées depuis chaque campagne concernée.
            $table->foreignId('campaign_id')->nullable()->constrained()->cascadeOnDelete();
            $table->foreignId('world_id')->nullable()->constrained()->cascadeOnDelete();
            $table->foreignId('game_system_id')->nullable()->constrained()->cascadeOnDelete();
            // Pas de clé étrangère sur l'élément : son historique survit à sa suppression.
            $table->string('subject_type', 30);
            $table->unsignedBigInteger('subject_id');
            $table->string('subject_label');
            $table->string('event', 10);
            $table->jsonb('diff');
            // Opérations groupées (un import) : même identifiant pour toutes leurs entrées.
            $table->uuid('batch')->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->index(['subject_type', 'subject_id']);
            $table->index(['campaign_id', 'id']);
            $table->index(['world_id', 'id']);
            $table->index(['game_system_id', 'id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('activity_logs');
    }
};
