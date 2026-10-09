<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Un champ peut concerner plusieurs types de fiche : entity_type_ids les liste tous
     * quand il y en a plusieurs, entity_type_id garde le premier (ou null pour tous les types).
     */
    public function up(): void
    {
        Schema::table('field_definitions', function (Blueprint $table) {
            $table->jsonb('entity_type_ids')->nullable()->after('entity_type_id');
        });

        DB::table('bug_reports')
            ->where('source', 'recette')
            ->whereIn('title', [
                "Un champ ne peut concerner qu'un seul type de fiche ou tous",
                'Un champ pour plusieurs types de fiche',
            ])
            ->where('status', 'new')
            ->update(['status' => 'fixed', 'fixed_in' => '0.41.0', 'updated_at' => now()]);
    }

    public function down(): void
    {
        Schema::table('field_definitions', function (Blueprint $table) {
            $table->dropColumn('entity_type_ids');
        });
    }
};
