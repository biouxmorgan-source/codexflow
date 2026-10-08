<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Clé d'API personnelle d'un MJ pour l'assistant IA : chiffrée (APP_KEY), jamais affichée ni exportée.
        // CodexFlow n'appelle une IA qu'avec cette clé, donc toujours aux frais de son propriétaire.
        Schema::table('users', function (Blueprint $table) {
            $table->string('ai_provider', 20)->nullable();
            $table->string('ai_model', 100)->nullable();
            $table->text('ai_api_key')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['ai_provider', 'ai_model', 'ai_api_key']);
        });
    }
};
