<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /** Backlog de la recette v0.34.0 livré en 0.38.0 : recherche. */
    public function up(): void
    {
        DB::table('bug_reports')
            ->where('source', 'recette')
            ->whereIn('title', [
                'Recherche avec racinisation française',
                'Recherche dans les mondes et jeux sans campagne',
            ])
            ->where('status', 'new')
            ->update(['status' => 'fixed', 'fixed_in' => '0.38.0', 'updated_at' => now()]);
    }
};
