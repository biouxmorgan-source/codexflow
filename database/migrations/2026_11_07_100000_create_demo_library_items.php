<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Bibliothèque de démos : des campagnes prêtes à jouer (souvent le jeu d'un auteur ou d'un éditeur,
        // récompense « Créateur de monde »), que tout MJ copie dans son compte en un clic.
        // Le contenu est figé dans une archive SagaWyn ; la campagne d'origine reste à l'administrateur.
        Schema::create('demo_library_items', function (Blueprint $table) {
            $table->id();
            $table->string('title', 150);
            $table->string('game_name', 150);
            $table->string('publisher', 150)->nullable();
            $table->text('summary')->nullable();
            $table->string('store_url', 500)->nullable();
            $table->string('locale', 5);
            $table->string('archive_path');
            $table->unsignedBigInteger('archive_size')->default(0);
            $table->foreignId('source_campaign_id')->nullable()->constrained('campaigns')->nullOnDelete();
            $table->boolean('published')->default(false);
            $table->unsignedInteger('loads_count')->default(0);
            $table->timestamps();
        });

        // La copie d'un MJ garde le lien vers sa démo, pour lui rappeler où acheter le jeu.
        Schema::table('campaigns', function (Blueprint $table) {
            $table->foreignId('demo_library_item_id')->nullable()->constrained()->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('campaigns', function (Blueprint $table) {
            $table->dropConstrainedForeignId('demo_library_item_id');
        });
        Schema::dropIfExists('demo_library_items');
    }
};
