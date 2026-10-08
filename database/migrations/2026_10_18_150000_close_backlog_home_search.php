<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /** Évolution de la recette V1 livrée en 0.21.0 : recherche depuis l'accueil. */
    public function up(): void
    {
        DB::table('bug_reports')
            ->where('source', 'recette')
            ->where('title', 'Recherche absente hors des pages d\'une campagne')
            ->update(['status' => 'fixed', 'fixed_in' => '0.21.0', 'updated_at' => now()]);
    }
};
