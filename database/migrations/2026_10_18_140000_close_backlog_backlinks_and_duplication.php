<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /** Évolutions de la recette V1 livrées en 0.20.0 : « Cité dans » étendu, duplication sans les statuts. */
    public function up(): void
    {
        DB::table('bug_reports')
            ->where('source', 'recette')
            ->whereIn('title', [
                'Rétroliens incomplets : scènes, chronologie et secrets n\'y figurent pas',
                'À trancher : l\'état « mort » d\'une fiche est copié lors de la duplication d\'une campagne',
            ])
            ->update(['status' => 'fixed', 'fixed_in' => '0.20.0', 'updated_at' => now()]);
    }
};
