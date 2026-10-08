<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /** Évolutions de la recette v0.25.0 livrées en 0.30.0 : écriture et liens. */
    public function up(): void
    {
        DB::table('bug_reports')
            ->where('source', 'recette')
            ->whereIn('title', [
                'Suggestions [[ ]] dans la note rapide de séance',
                'Éditeur riche partout',
                'Homonymes après copie ou import',
                'Liens [[ ]] dans les champs texte long de la fiche joueur',
            ])
            ->update(['status' => 'fixed', 'fixed_in' => '0.30.0', 'updated_at' => now()]);
    }
};
