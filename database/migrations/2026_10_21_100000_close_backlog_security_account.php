<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /** Évolutions de la recette v0.25.0 livrées en 0.27.0 (aide, e-mails) et 0.29.0 (sécurité et compte). */
    public function up(): void
    {
        $fixed = [
            '0.27.0' => ['Aide publique et volet compte'],
            '0.28.0' => ['E-mails à l\'image de LoreMundi'],
            '0.29.0' => [
                'Vérification de la nouvelle adresse e-mail',
                'Limiteur des formulaires de compte trop large',
                'CSP plus stricte',
                'Messages du joueur supprimé',
                'Envoi push réel non vérifiable ici, prérequis non documenté',
            ],
        ];

        foreach ($fixed as $version => $titles) {
            DB::table('bug_reports')
                ->where('source', 'recette')
                ->whereIn('title', $titles)
                ->update(['status' => 'fixed', 'fixed_in' => $version, 'updated_at' => now()]);
        }
    }
};
