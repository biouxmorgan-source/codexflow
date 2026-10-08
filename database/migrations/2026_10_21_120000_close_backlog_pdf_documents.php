<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /** Évolutions de la recette v0.25.0 livrées en 0.32.0 : PDF et documents. */
    public function up(): void
    {
        DB::table('bug_reports')
            ->where('source', 'recette')
            ->whereIn('title', [
                'Image d\'un jeu et d\'un monde',
                'Lecteur PDF intégré',
                '« Utilisé par » d\'un document',
                'Feuille PDF par pdf.js avec son nom d\'origine',
                'PDF à la table via l\'iframe du navigateur',
            ])
            ->update(['status' => 'fixed', 'fixed_in' => '0.32.0', 'updated_at' => now()]);
    }
};
