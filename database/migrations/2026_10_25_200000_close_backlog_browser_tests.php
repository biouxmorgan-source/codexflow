<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /** Backlog de la recette v0.34.0 livré en 0.43.0 : tests navigateur. */
    public function up(): void
    {
        DB::table('bug_reports')
            ->where('source', 'recette')
            ->whereIn('title', [
                'Test automatisé du repli à 30 secondes',
                'Tests navigateur des parcours transverses',
            ])
            ->where('status', 'new')
            ->update(['status' => 'fixed', 'fixed_in' => '0.43.0', 'updated_at' => now()]);
    }
};
