<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /** Backlog de la recette v0.34.0 livré en 0.37.0 : joueurs et rôles. */
    public function up(): void
    {
        DB::table('bug_reports')
            ->where('source', 'recette')
            ->whereIn('title', [
                'Illustrations et relations publiques invisibles du joueur',
                'Co-MJ sans accès au jeu, au monde et aux champs',
                "Notifications d'un co-MJ rétrogradé",
                'Export par le co-MJ',
                'Fil de campagne pour les joueurs',
            ])
            ->where('status', 'new')
            ->update(['status' => 'fixed', 'fixed_in' => '0.37.0', 'updated_at' => now()]);
    }
};
