<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /** Backlog de la recette v0.34.0 livré en 0.36.0 : confort du MJ et de la séance. */
    public function up(): void
    {
        DB::table('bug_reports')
            ->where('source', 'recette')
            ->whereIn('title', [
                "Statut visible dans l'en-tête de la fiche",
                "Piloter les pages d'un PDF depuis la télécommande",
                "Tourner les pages d'un PDF depuis la télécommande",
                'Indicateur de page dans le lecteur PDF',
                '« Lui rendre » absent quand le personnage a été confié à un autre joueur entre-temps',
                'Conversation privée retrouvée par le joueur qui revient',
                'Noter un événement joué depuis le Mode Session',
                'Prétirés distingués des PNJ',
                'Mondes homonymes après plusieurs démonstrations',
                'Liste des éléments tagués depuis la page Tags',
                'Spectateur et écran de table non partagé',
            ])
            ->where('status', 'new')
            ->update(['status' => 'fixed', 'fixed_in' => '0.36.0', 'updated_at' => now()]);
    }
};
