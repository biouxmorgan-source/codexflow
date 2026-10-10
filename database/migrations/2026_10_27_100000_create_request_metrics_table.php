<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Temps de réponse du serveur, agrégés par heure : aucun détail sur la requête ni la personne.
        Schema::create('request_metrics', function (Blueprint $table) {
            $table->id();
            $table->timestamp('hour')->unique();
            $table->unsignedInteger('requests')->default(0);
            $table->unsignedBigInteger('total_ms')->default(0);
            $table->unsignedInteger('max_ms')->default(0);
            $table->unsignedInteger('slow')->default(0);
            $table->unsignedInteger('very_slow')->default(0);
            $table->unsignedInteger('errors')->default(0);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('request_metrics');
    }
};
