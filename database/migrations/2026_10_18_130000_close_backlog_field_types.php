<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /** Évolution de la recette V1 livrée en 0.19.0 : champs lien, fichier et référence à une fiche. */
    public function up(): void
    {
        DB::table('bug_reports')
            ->where('source', 'recette')
            ->where('title', 'Champs de type lien, fichier et référence à une fiche absents')
            ->update(['status' => 'fixed', 'fixed_in' => '0.19.0', 'updated_at' => now()]);
    }
};
