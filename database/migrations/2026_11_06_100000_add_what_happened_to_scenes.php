<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // La description reste « Ce qui est prévu » ; « Ce qui s'est passé » raconte la table.
        // « Jouée comme prévu » évite de recopier la préparation quand la table l'a suivie.
        Schema::table('scenes', function (Blueprint $table) {
            $table->text('what_happened')->nullable();
            $table->boolean('played_as_planned')->default(false);
        });

        // Livre des aventures : la version rédigée par le MJ (ou son IA), à côté du livre assemblé.
        Schema::table('campaigns', function (Blueprint $table) {
            $table->text('adventure_book')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('scenes', function (Blueprint $table) {
            $table->dropColumn(['what_happened', 'played_as_planned']);
        });

        Schema::table('campaigns', function (Blueprint $table) {
            $table->dropColumn('adventure_book');
        });
    }
};
