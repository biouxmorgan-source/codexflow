<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Habillage de l'écran de table : purement visuel, choisi par le MJ pour coller au ton du jeu.
        Schema::table('campaigns', function (Blueprint $table) {
            $table->string('table_theme', 20)->default('nuit');
        });
    }

    public function down(): void
    {
        Schema::table('campaigns', function (Blueprint $table) {
            $table->dropColumn('table_theme');
        });
    }
};
