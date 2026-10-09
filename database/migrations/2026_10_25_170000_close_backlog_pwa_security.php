<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /** Backlog de la recette v0.34.0 livré en 0.40.0 : application installée et sécurité. */
    public function up(): void
    {
        DB::table('bug_reports')
            ->where('source', 'recette')
            ->whereIn('title', [
                'CSP plus stricte',
                'Manifeste traduit',
                'Mode lecture hors ligne',
            ])
            ->where('status', 'new')
            ->update(['status' => 'fixed', 'fixed_in' => '0.40.0', 'updated_at' => now()]);
    }
};
