<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Chronologie d'une campagne : histoire du monde, événements prévus, événements joués.
        // La date est un libellé libre (« 12 mars 1924 », « Jour 3 », « Nuit 2 ») ; l'ordre est choisi par le MJ.
        Schema::create('timeline_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('campaign_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('kind', 10);
            $table->string('date_label', 100)->nullable();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('zone', 10)->default('gm');
            $table->foreignId('play_session_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('scene_id')->nullable()->constrained()->nullOnDelete();
            $table->unsignedInteger('position');
            $table->timestamps();

            $table->index(['campaign_id', 'position']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('timeline_events');
    }
};
