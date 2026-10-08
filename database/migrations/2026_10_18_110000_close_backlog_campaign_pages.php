<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /** Évolutions de la recette V1 livrées en 0.18.0 : transfert de personnage, Mes campagnes, pages jeu et monde. */
    public function up(): void
    {
        DB::table('bug_reports')
            ->where('source', 'recette')
            ->whereIn('title', [
                'Pas d\'écran pour choisir ce qui passe à un nouveau personnage',
                'Pas d\'archivage de campagne, pas de bouton « Reprendre », dernière séance absente des cartes',
                'Pas de page dédiée à un jeu ni à un monde',
            ])
            ->update(['status' => 'fixed', 'fixed_in' => '0.18.0', 'updated_at' => now()]);
    }
};
