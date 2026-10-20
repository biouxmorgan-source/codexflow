<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Lancers enregistrés d'une feuille personnelle : [{id, label, formula}], comme « Mes lancers » d'une campagne.
        Schema::table('personal_characters', function (Blueprint $table) {
            $table->jsonb('dice_presets')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('personal_characters', function (Blueprint $table) {
            $table->dropColumn('dice_presets');
        });
    }
};
