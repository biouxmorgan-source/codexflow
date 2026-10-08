<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /** Évolutions de la recette V1 livrées en 0.17.0 : éditeur riche et notes des joueurs. */
    public function up(): void
    {
        DB::table('bug_reports')
            ->where('source', 'recette')
            ->whereIn('title', [
                'Pas de pdf.js ni d\'éditeur riche (Tiptap)',
                'Notes des joueurs : pas d\'autocomplétion `[[`, pas de vue par séance pour le MJ',
            ])
            ->update(['status' => 'fixed', 'fixed_in' => '0.17.0', 'updated_at' => now()]);
    }
};
