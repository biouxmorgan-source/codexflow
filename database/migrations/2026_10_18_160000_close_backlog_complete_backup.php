<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /** Évolution de la recette V1 livrée en 0.22.0 : sauvegarde complète de la campagne. */
    public function up(): void
    {
        DB::table('bug_reports')
            ->where('source', 'recette')
            ->where('title', 'Archive de campagne sans joueurs, séances ni messages')
            ->update(['status' => 'fixed', 'fixed_in' => '0.22.0', 'updated_at' => now()]);
    }
};
