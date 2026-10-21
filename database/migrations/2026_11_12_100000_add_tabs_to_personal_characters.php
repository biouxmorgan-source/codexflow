<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Onglets nommés d'une feuille personnelle : [{id, name}]. Chaque bloc porte l'onglet où il
        // s'affiche ; une feuille sans onglet en a un, implicite, qui reçoit tous ses blocs.
        Schema::table('personal_characters', function (Blueprint $table) {
            $table->jsonb('tabs')->default('[]');
        });
    }

    public function down(): void
    {
        Schema::table('personal_characters', function (Blueprint $table) {
            $table->dropColumn('tabs');
        });
    }
};
