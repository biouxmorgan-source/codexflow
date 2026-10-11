<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Contenu maison : sans coche, la fiche est officielle. La version officielle remplacée reste en zone MJ.
        Schema::table('entities', function (Blueprint $table) {
            $table->boolean('is_house')->default(false);
            $table->text('official_version')->nullable();
        });

        // Les règles ont déjà leur origine (« Maison ») : il ne leur manque que la version officielle.
        Schema::table('rules', function (Blueprint $table) {
            $table->text('official_version')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('entities', function (Blueprint $table) {
            $table->dropColumn(['is_house', 'official_version']);
        });

        Schema::table('rules', function (Blueprint $table) {
            $table->dropColumn('official_version');
        });
    }
};
