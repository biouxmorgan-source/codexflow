<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /** Backlog de la recette v0.34.0 livré en 0.39.0 : compte et administration. */
    public function up(): void
    {
        DB::table('bug_reports')
            ->where('source', 'recette')
            ->whereIn('title', [
                "Expliquer l'absence de paiement",
                'Dates de connexion dans la console',
                'Signaler un problème sans compte',
                "Alerte d'administration sur l'envoi push",
            ])
            ->where('status', 'new')
            ->update(['status' => 'fixed', 'fixed_in' => '0.39.0', 'updated_at' => now()]);
    }
};
