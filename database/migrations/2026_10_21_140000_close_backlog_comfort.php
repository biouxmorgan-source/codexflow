<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /** Évolutions de la recette v0.25.0 livrées en 0.33.0 : finitions. */
    public function up(): void
    {
        DB::table('bug_reports')
            ->where('source', 'recette')
            ->whereIn('title', [
                'Extrait de recherche',
                'Lisibilité du graphe',
                'Raccourci « toute la table » dans le Donner libre',
                'Reprendre d\'un ancien personnage après un retrait',
                'Pas de test navigateur des boutons de la fenêtre « reçu »',
                'Repli sans Reverb non retesté',
                'Noms internes « codexflow »',
            ])
            ->update(['status' => 'fixed', 'fixed_in' => '0.33.0', 'updated_at' => now()]);
    }
};
